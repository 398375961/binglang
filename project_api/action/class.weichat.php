<?PHP
/*
+----------------------------------------------------------------------
| SPF-简单的PHP框架 1.0 测试版
+----------------------------------------------------------------------
| Copyright (c) 2012-2016 All rights reserved.
+----------------------------------------------------------------------
| Licensed ( http:www.apache.org/licenses/LICENSE-2.0 )
+----------------------------------------------------------------------
| Author: lufeng <lufengreat@163.com>
+----------------------------------------------------------------------
| 礼品机领奖api
*/
class weichatAction extends Action{

	/* 发奖接口，提供两种接口 
	由直接传递微信公众号和客户端的
	  code 验证码 client 用户微信账号 weichat 商户公众号
	由用户输入验证码的情况
	 step 1, 填写验证码
	 step 2, 通知机器出货
	*/
	public function qr_prize(){
		if($_REQUEST['code'] && $_REQUEST['client'] && $_REQUEST['weichat']){
			//微信接口调用
			$where = array(
				array('code',$_REQUEST['code']),
				array('weichat',$_REQUEST['weichat']),
			);
			$item = M('weichat')->where($where)->find();
			if(!is_array($item) || count($item) < 1 || $item['p_type'] != 1){
				echo json_encode(array('res' => 0,'msg' => '不存在或者不允许由公众平台直接调用，请确认验证码是否输入正确！'));
				return;
			};
			if($item['status'] == 0){
				echo json_encode(array('res' => 0,'msg' => '公众号已锁定，请激活!'));
				return;
			}
			if($item['status'] != 2){
				//检查是否已经送出过礼品
				$where = array(
					array('weichat',$item['weichat']),
					array('clientname',$_REQUEST['client'])
				);
				$p_log = M('prizelog')->where($where)->find();
				if(is_array($p_log) && count($p_log) > 0){
					echo json_encode(array('res' => 0,'msg' => '您已经领取过奖品了，请不要重复领奖哦'));
					return;
				}
			}
			//检查机器是否登录，如果没登录 不送出礼品
			if(Model('prize')->check_machine($item['vmid']) === false){
				echo json_encode(array('res' => 0,'msg' => '无法连接机器出货，请稍后再试'));
				return;
			}
			//通知机器发货...
			if(Model('prize')->push_prize($item,$_REQUEST['client'])){
				echo json_encode(array('res' => 1,'msg' => '礼品已送出，请从取物口拿出礼品'));
			}else{
				echo json_encode(array('res' => 0,'msg' => '通知机器出货失败'));
			}
			return;
		}
		//浏览器访问
	//	if(stripos($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') === false){
	//		echo '请用微信访问';
	//		return; //非微信调用，不做处理
	//	}
		$cookie_name = 'qr_prize_code';
		$client = get_cookie($cookie_name);
		if(empty($client)){
			$rand = md5(rand(10,99).microtime(true).rand(10,99));
			set_cookie($cookie_name,$rand,365*86400);
		}
		if(IS_POST){
			$code = $_POST['code'];
			if($_POST['wx'] != 1){
				echo '请用微信访问';
				return;
			}
			// php 判断是否为 ajax 请求
			if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){ 
				// ajax 请求的处理方式 
			}else{ 
				echo '非法请求！';
				return;
			};
			$where = array(
				array('code',$code),
				array('weichat',$_POST['weichat']),
			);
			$item = M('weichat')->where($where)->find();
			if(!is_array($item) || count($item) < 1 || $item['p_type'] != 0){
				echo '验证码输入不正确！';
				return;
			};
			if($item['status'] == 0){
				echo '公众号已锁定，请激活!';
				return;
			}
			if($item['status'] != 2){
				//检查是否已经送出过礼品
				$where = array(
					array('weichat',$item['weichat']),
					array('clientname',$client)
				);
				$p_log = M('prizelog')->where($where)->find();
				if(is_array($p_log) && count($p_log) > 0){
					echo '您已经领取过奖品了，请不要重复领奖哦';
					return;
				}
			}
			//检查机器是否登录，如果没登录 不送出礼品
			if(Model('prize')->check_machine($item['vmid']) === false){
				echo '无法连接机器出货，请稍后再试';
				return;
			}
			//通知机器发货...
			$res = Model('prize')->push_prize($item,$client);
			if($res === true){
				echo '礼品已送出，请从取物口拿出礼品';
			}else{
				echo $res;
			}
			return;
		}else{
			//获取广告配置
			$where = array(
				array('weichat',$_REQUEST['weichat']),
			);
			$item = M('weichat')->where($where)->select_one();
			$vmid = $item['vmid'];
			$machine = M('machine')->where(array('vmid',$vmid))->find();
			$user_id = intval($machine['user_id']);
			$file = ROOT_PWC.'cache/prize_ad_config_'.$user_id.'.php';
			if(file_exists($file)){
				$cfg = include($file);
				$this->set('top_ad',$cfg['top_ad']);
				$this->set('bottom_ad',$cfg['bottom_ad']);
			}
		}
		$this->tpl();
	}
}