<?php
include(ROOT_PWC.'project_admin/include/function.php');

//获取相关支付配置
function get_pay_cfg($vmid,$pay_type,$user_id = 0){
	$pay_cfg = array(
		'PAY_SELF'			=> 0,// 0 金吉（或上级代理）代收 1自己接口  2分账
		'INCOME_USERID'		=> 1,// 收款账号uid,入账用户id
		'USER_ID'			=> 0,// 金吉收款的记账uid
		'MACHINE_UID'		=> 0,// 机器直接管理账户uid
		'PAY_ZHEKOU'		=> 1,// 折扣 1表示不打折
	);
	if($user_id){
		$machine = array('user_id' => $user_id);
	}else{
		$machine = M('machine')->fields('user_id')->where(array('vmid',$vmid))->find();
	}
	if(!is_array($machine)) exit('no machine config!');
	$pay_cfg['USER_ID'] = $machine['user_id'];
	$pay_cfg['MACHINE_UID'] = $machine['user_id'];
	if($machine['user_id'] > 0){
		//个人支付配置
		$where = array(array('uid',intval($machine['user_id'])),array('cfg_type','pay'));
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg2[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay'){
			if(!empty($pay_cfg2['ALIPAY_ZHEKOU'])) $pay_cfg['PAY_ZHEKOU'] = $pay_cfg2['ALIPAY_ZHEKOU'];
			if(!empty($pay_cfg2['ALIPAY_PARTNER'])){
				if(!empty($pay_cfg2['ALIPAY_KEY'])){
					//使用自己的接口
					$pay_cfg['PAY_SELF']		= 1; 
					$pay_cfg['INCOME_USERID']	= $machine['user_id']; 
					$pay_cfg['ALIPAY_PARTNER']	= $pay_cfg2['ALIPAY_PARTNER'];
					$pay_cfg['ALIPAY_KEY']		= $pay_cfg2['ALIPAY_KEY'];
					$pay_cfg['ALIPAY_SALER']	= $pay_cfg2['ALIPAY_SALER'];
					$pay_cfg['ALIPAY_APP_ID']	= $pay_cfg2['ALIPAY_APP_ID'];
					return $pay_cfg;
				}else{
					//分账
					$pay_cfg['PAY_SELF']		= 2; 
					$pay_cfg['INCOME_USERID']	= $machine['user_id']; 
					$pay_cfg['ALIPAY_ROAYLTY']  = $pay_cfg2['ALIPAY_PARTNER'];
				}
			}
		}
		if($pay_type === 'weixin'){
			if(!empty($pay_cfg2['WEIXIN_ZHEKOU'])) $pay_cfg['PAY_ZHEKOU'] = $pay_cfg2['WEIXIN_ZHEKOU'];
			if(!empty($pay_cfg2['WEIXIN_APPID'])){
				//微信使用自己的接口
				$pay_cfg['PAY_SELF']		= 1; 
				$pay_cfg['INCOME_USERID']	= $machine['user_id']; 
				$pay_cfg['WEIXIN_APPID']	= $pay_cfg2['WEIXIN_APPID'];
				$pay_cfg['WEIXIN_MCHID']	= $pay_cfg2['WEIXIN_MCHID'];
				$pay_cfg['WEIXIN_KEY']		= $pay_cfg2['WEIXIN_KEY'];
				$pay_cfg['WEIXIN_APPSECRET']= $pay_cfg2['WEIXIN_APPSECRET'];
				return $pay_cfg;
			}
		}
	}
	//执行到这里说明必定是由上级代收（或者是金吉代收，分账）
	$user = M('users')->where(array('id',intval($machine['user_id'])))->find();
	if($user['parent_id'] > 1){
		$user_parent = M('users')->where(array('id',$user['parent_id']))->find();
	}
	//如果上级设置了代收就用上级，否则就用金吉的收款配置
	if($user_parent['pay_self'] == 1){
		$pay_cfg['USER_ID'] = $user_parent['id']; //金吉代收的时候用到
		$where = array(array('uid',$user_parent['id']),array('cfg_type','pay'));
		//上级代收不分账
		if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['INCOME_USERID'] = $user_parent['id']; 
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg_p[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay' && (!empty($pay_cfg_p['ALIPAY_PARTNER']))){
			if(!empty($pay_cfg_p['ALIPAY_KEY'])){
				//上级使用的是 支付宝商户版
				if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['PAY_SELF'] = 1; 
				$pay_cfg['ALIPAY_PARTNER']	= $pay_cfg_p['ALIPAY_PARTNER'];
				$pay_cfg['ALIPAY_KEY']		= $pay_cfg_p['ALIPAY_KEY'];
				$pay_cfg['ALIPAY_SALER']	= $pay_cfg_p['ALIPAY_SALER'];
				$pay_cfg['ALIPAY_APP_ID']	= $pay_cfg_p['ALIPAY_APP_ID'];
				return $pay_cfg;
			}
			if($pay_cfg['PAY_SELF'] == 0){
				//自己没有配置分账，且 上级使用的是 支付宝分账号
				$pay_cfg['PAY_SELF']		= 2; 
				$pay_cfg['ALIPAY_ROAYLTY']  = $pay_cfg_p['ALIPAY_PARTNER'];
			}
		}
		if($pay_type === 'weixin' && (!empty($pay_cfg_p['WEIXIN_APPID']))){
			$pay_cfg['PAY_SELF'] = 1; 
			$pay_cfg['WEIXIN_APPID']	= $pay_cfg_p['WEIXIN_APPID'];
			$pay_cfg['WEIXIN_MCHID']	= $pay_cfg_p['WEIXIN_MCHID'];
			$pay_cfg['WEIXIN_KEY']		= $pay_cfg_p['WEIXIN_KEY'];
			$pay_cfg['WEIXIN_APPSECRET']= $pay_cfg_p['WEIXIN_APPSECRET'];
			return $pay_cfg;
		}
		//执行到这里，说明肯定是自己没有配置，上级又代收，但是上级也没有设置了 或者是自己和上级都是个人支付宝账号
		//使用网站配置的收款方式
		$where = array(array('uid',1),array('cfg_type','pay'));
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg[$a['cfg_name']] = $a['cfg_value'];
		//金吉代收，且用户没有设置分账
		if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['INCOME_USERID'] = 1;
		return $pay_cfg;
	}else{
		//金吉代收支付配置
		$where = array(array('uid',1),array('cfg_type','pay')); 
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg_p[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay'){
			//上级使用的是 支付宝商户版
			$pay_cfg['ALIPAY_PARTNER']	= $pay_cfg_p['ALIPAY_PARTNER'];
			$pay_cfg['ALIPAY_KEY']		= $pay_cfg_p['ALIPAY_KEY'];
			$pay_cfg['ALIPAY_SALER']	= $pay_cfg_p['ALIPAY_SALER'];
			$pay_cfg['ALIPAY_APP_ID']	= $pay_cfg_p['ALIPAY_APP_ID'];
			return $pay_cfg;
		}
		if($pay_type === 'weixin'){
			$pay_cfg['WEIXIN_APPID']	= $pay_cfg_p['WEIXIN_APPID'];
			$pay_cfg['WEIXIN_MCHID']	= $pay_cfg_p['WEIXIN_MCHID'];
			$pay_cfg['WEIXIN_KEY']		= $pay_cfg_p['WEIXIN_KEY'];
			$pay_cfg['WEIXIN_APPSECRET']= $pay_cfg_p['WEIXIN_APPSECRET'];
			return $pay_cfg;
		}
	}
}