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
*/
class IndexAction extends Action{
	protected function before(){
		if(METHOD != 'login' && !is_login()) output('请先登录!','?m=login',2);
	}
	
	//校园卡管理
	public function index(){
		$cardid = $_REQUEST['cardid'];
		if(empty($cardid)){
			//查询我下面的校园卡
			$parents = get_session('parents');
			$where = array('client_id',$parents['cardid']);
			$list = M('school_card')->where($where)->select();
			$this->set('parents',$parents);
			$this->set('list',$list);
			$this->tpl('school_cards');
			return;
		}
		//查询某校园卡的消费记录
		$where = array('salecard',$cardid);
		$ar = array(
			'pagesize' => 15,
			'base_url' => array(
				'a'			=> ACTION,
				'm'			=> METHOD,
				'cardid'	=> $cardid
			));
		$fields = 'saletime,salemoney,goods_name';
		$data = M('saledetail')->fields($fields)->order('id','DESC')->where($where)->page_and_list($ar);
		$this->set('list',$data['list']);
		$this->set('str_page',$data['str_page']);
		$this->tpl();
	}

	//资金流水
	public function flow(){
		$parents = get_session('parents');
		$where = array();
		$cardid = $_REQUEST['cardid'];
		$where[] = array('sc.client_id',$parents['cardid']);
		$where[] = array('cl.cardid',$cardid);
		$ar = array('pagesize' => 20);
		$ar['base_url'] = array(
			'a'				=> ACTION,
			'm'				=> METHOD,
			'cardid'		=> $cardid
		);
		$table = '##school_card_log cl JOIN ##school_card sc ON sc.cardid=cl.cardid';
		$fields = 'cl.*';
		$data = M()->table($table)->fields($fields)->where($where)->order('cl.id','DESC')->page_and_list($ar);
		$this->set('list',$data['list']);
		$this->set('order',$order);
		$this->set('str_page',$data['str_page']);
		$this->tpl();
	}

	//设置卡片信息
	function setinfo(){
		$parents = get_session('parents');
		$cardid = $_REQUEST['cardid'];
		$password = $_REQUEST['password'];
		if(strlen($password) > 0){
			if(!is_numeric($password)) output('卡片密码只能是数字');
			if(strlen($password) < 4 || strlen($password) > 6) output('卡片密码长度为4到6个数字');
		}
		$where = array('cardid',$cardid);
		$item = M('school_card')->where($where)->find();
		if($item['client_id'] != $parents['cardid']) output('非法设置！');
		if(IS_POST){
			$day_limit = intval($_POST['day_limit'])*100;
			if($day_limit < 0) $day_limit = 0;
			$goods_allow = implode(',',$_POST['goods_allow']);
			$goods_fobid = implode(',',$_POST['goods_fobid']);
			$data = array(
				'status'	=> intval($_POST['status']),
				'mobile'	=> $_POST['mobile'],
				'day_limit'	=> $day_limit,
				'password'	=> $password,
				'goods_allow'=> $goods_allow,
				'goods_fobid'=> $goods_fobid,
			);
			M('school_card')->where($where)->save($data);
			output('保存成功！');
		}
		//查询所有商品
		$where = array('user_id',$item['user_id']);
		$goods = M('goods')->where($where)->order('goods_type')->select();
		$this->set('goods',$goods);
		$this->set('item',$item);
		$this->tpl();
	}

	//修改密码
	public function pwd(){
		if(IS_POST){
			$parents = get_session('parents');
			if(md5($_POST['password']) != $parents['password']) output('密码输入不正确！');
			if($_POST['newpwd'] != $_POST['newpwd2']) output('两次输入的新密码不一致！');
			if(strlen($_POST['newpwd']) < 6 || strlen($_POST['newpwd']) > 12) output('密码长度为6到12个字符！');
			$res = M('parents')->where(array('cardid',$parents['cardid']))->save(array('password' => md5($_POST['newpwd'])));
			$res > 0 ? output('密码修改成功！请重新登录！','?a=index&m=logout',3) : output('密码修改失败！');
		}
		$this->tpl();
	}

	public function login(){
		if(is_login()){
			output('登录成功','?a=index');
		}
		if(IS_POST) do_login();
		$this->tpl();
	}

	public function logout(){
		do_logout();
	}

}