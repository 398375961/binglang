<?php
include(ROOT_PWC.'project_admin/include/function.php');

//��ȡ���֧������
function get_pay_cfg($vmid,$pay_type,$user_id = 0){
	$pay_cfg = array(
		'PAY_SELF'			=> 0,// 0 �𼪣����ϼ��������� 1�Լ��ӿ�  2����
		'INCOME_USERID'		=> 1,// �տ��˺�uid,�����û�id
		'USER_ID'			=> 0,// ���տ�ļ���uid
		'MACHINE_UID'		=> 0,// ����ֱ�ӹ����˻�uid
		'PAY_ZHEKOU'		=> 1,// �ۿ� 1��ʾ������
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
		//����֧������
		$where = array(array('uid',intval($machine['user_id'])),array('cfg_type','pay'));
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg2[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay'){
			if(!empty($pay_cfg2['ALIPAY_ZHEKOU'])) $pay_cfg['PAY_ZHEKOU'] = $pay_cfg2['ALIPAY_ZHEKOU'];
			if(!empty($pay_cfg2['ALIPAY_PARTNER'])){
				if(!empty($pay_cfg2['ALIPAY_KEY'])){
					//ʹ���Լ��Ľӿ�
					$pay_cfg['PAY_SELF']		= 1; 
					$pay_cfg['INCOME_USERID']	= $machine['user_id']; 
					$pay_cfg['ALIPAY_PARTNER']	= $pay_cfg2['ALIPAY_PARTNER'];
					$pay_cfg['ALIPAY_KEY']		= $pay_cfg2['ALIPAY_KEY'];
					$pay_cfg['ALIPAY_SALER']	= $pay_cfg2['ALIPAY_SALER'];
					$pay_cfg['ALIPAY_APP_ID']	= $pay_cfg2['ALIPAY_APP_ID'];
					return $pay_cfg;
				}else{
					//����
					$pay_cfg['PAY_SELF']		= 2; 
					$pay_cfg['INCOME_USERID']	= $machine['user_id']; 
					$pay_cfg['ALIPAY_ROAYLTY']  = $pay_cfg2['ALIPAY_PARTNER'];
				}
			}
		}
		if($pay_type === 'weixin'){
			if(!empty($pay_cfg2['WEIXIN_ZHEKOU'])) $pay_cfg['PAY_ZHEKOU'] = $pay_cfg2['WEIXIN_ZHEKOU'];
			if(!empty($pay_cfg2['WEIXIN_APPID'])){
				//΢��ʹ���Լ��Ľӿ�
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
	//ִ�е�����˵���ض������ϼ����գ������ǽ𼪴��գ����ˣ�
	$user = M('users')->where(array('id',intval($machine['user_id'])))->find();
	if($user['parent_id'] > 1){
		$user_parent = M('users')->where(array('id',$user['parent_id']))->find();
	}
	//����ϼ������˴��վ����ϼ���������ý𼪵��տ�����
	if($user_parent['pay_self'] == 1){
		$pay_cfg['USER_ID'] = $user_parent['id']; //�𼪴��յ�ʱ���õ�
		$where = array(array('uid',$user_parent['id']),array('cfg_type','pay'));
		//�ϼ����ղ�����
		if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['INCOME_USERID'] = $user_parent['id']; 
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg_p[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay' && (!empty($pay_cfg_p['ALIPAY_PARTNER']))){
			if(!empty($pay_cfg_p['ALIPAY_KEY'])){
				//�ϼ�ʹ�õ��� ֧�����̻���
				if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['PAY_SELF'] = 1; 
				$pay_cfg['ALIPAY_PARTNER']	= $pay_cfg_p['ALIPAY_PARTNER'];
				$pay_cfg['ALIPAY_KEY']		= $pay_cfg_p['ALIPAY_KEY'];
				$pay_cfg['ALIPAY_SALER']	= $pay_cfg_p['ALIPAY_SALER'];
				$pay_cfg['ALIPAY_APP_ID']	= $pay_cfg_p['ALIPAY_APP_ID'];
				return $pay_cfg;
			}
			if($pay_cfg['PAY_SELF'] == 0){
				//�Լ�û�����÷��ˣ��� �ϼ�ʹ�õ��� ֧�������˺�
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
		//ִ�е����˵���϶����Լ�û�����ã��ϼ��ִ��գ������ϼ�Ҳû�������� �������Լ����ϼ����Ǹ���֧�����˺�
		//ʹ����վ���õ��տʽ
		$where = array(array('uid',1),array('cfg_type','pay'));
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg[$a['cfg_name']] = $a['cfg_value'];
		//�𼪴��գ����û�û�����÷���
		if($pay_cfg['PAY_SELF'] == 0) $pay_cfg['INCOME_USERID'] = 1;
		return $pay_cfg;
	}else{
		//�𼪴���֧������
		$where = array(array('uid',1),array('cfg_type','pay')); 
		$cfg = M('sys_cfg')->where($where)->select();
		foreach($cfg as $a) $pay_cfg_p[$a['cfg_name']] = $a['cfg_value'];
		if($pay_type === 'alipay'){
			//�ϼ�ʹ�õ��� ֧�����̻���
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