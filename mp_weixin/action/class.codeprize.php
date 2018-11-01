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
| 通过输入验证码，送出礼品
*/
class CodeprizeAction extends Action{

	//验证码输入软键盘
	public function code(){
		$PlayerName = $_REQUEST['PlayerName'];
		if(!empty($PlayerName)){
			$vmid = substr($PlayerName,3);
			while(strlen($vmid) < 10) $vmid = '0'.$vmid;
		}else{
			$vmid = '';
		}
		$this->set('vmid',$vmid);
		$tpl = 'code';
		if($_GET['tpl']) $tpl .= '_'.$_GET['tpl'];
		$this->tpl($tpl);
	}

	//通过唯一验证码，领取奖品
	public function ajax_prize_code(){
		$code = $_REQUEST['code'];
		$vmid = $_REQUEST['vmid'];
		$exp_time = 86400; //验证码过期时间
		if(strlen($code) != 6) exit('验证码输入有误！');
		//查询验证码
		$item = M('rand_code')->where(array('rand_code',intval($code)))->find();
		if(!$item) exit('验证码输入有误！');
		if($item['createtime'] < NOW_TIME - $exp_time) exit('验证码已过期！');
		//查询礼品配置
		$where = array('wa.id',$item['scene_id']);
		$table = '##weixin_vmid wa JOIN ##weixin_mp wm ON wm.id=wa.weichatid';
		$fields = 'wa.*,wm.weichat_name,wm.weichat,wm.token';
		$prize_cfg = M()->table($table)->fields($fields)->where($where)->find();
		if(!$prize_cfg){
			exit('关注送礼品活动已结束！');
		}
		if($prize_cfg['status'] == 0){
			echo '公众号已锁定，请激活!';
			return;
		}
		if($prize_cfg['status'] == 1 && $item['has_prize'] == 1) exit('您已经领取过奖品了，请勿重复领奖！');
		//通知机器出货
		$res = Model('prize')->push_prize($prize_cfg,$item['client']);
		if($res === true){
			M('rand_code')->where(array('id',$item['id']))->save(array('has_prize' => 1));
			echo '领奖成功，请在出货口领取礼品...';
		}else{
			echo $res;
		}
	}
}