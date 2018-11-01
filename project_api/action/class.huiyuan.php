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
| 会员数据处理
+----------------------------------------------------------------------
*/
class HuiyuanAction extends Action{

	private $debug = false;

	/*
		会员卡销售上报
		业务流程：扫码刷会员卡（虚拟卡）->消费->记录会员卡消费记录
		@parm vmid   机器编号
			  money  消费金额
			  saleid 交易号
			  cardid 会员卡号
	*/
	public function sale(){
		if($this->debug){
			$vmid = $_REQUEST['vmid'];
			$money = intval($_REQUEST['money']);
			$saleid = $_REQUEST['saleid'];
			$cardid = $_REQUEST['cardid'];
			$score = intval($money);
		}else{
			$vmid = $_POST['vmid'];
			$money = intval($_POST['money']);
			$saleid = $_POST['saleid'];
			$cardid = $_POST['cardid'];
			$score = floor($money/100);
		}
		if($score < 1){
			exit('不能产生积分');
		}
		//查询卡片信息
		$where = ['card_id',$cardid];
		$member = M('hy_member')->where($where)->find();
		if(!$member){
			exit('会员卡不存在');
		}
		//增加积分记录
		$ar = ['score' => $score + $member['score']];
		$res = M('hy_member')->where($where)->save($ar);
		if(!$res) return;
		$note = '机器'.$vmid.'消费送积分，交易号:'.$saleid;
		$ar = [
			'cardid'		=> $cardid,
			'before_score'	=> $member['score'],
			'score'			=> $score,
			'create_time'	=> NOW_TIME,
			'note'			=> $note
		];
		M('hy_scorelog')->add($ar);
		//修改销售记录，将会员卡卡号补上
		$ar = ['salecard' => $cardid];
		$where = [
			['vmid',$vmid],
			['saleid',$saleid]
		];
		M('saledetail')->where($where)->save($ar);
		echo 'ok';
	}

}