<?php
function is_login(){
	return is_array(get_session('parents'));
}

//登录
function do_login($ret = false){
	$username = addslashes($_POST['username']);
	if(empty($username)) output('请输入身份证号码！');
	set_cookie('username',$username,30*86400);
	$password = md5($_POST['password']); //密码
	$w = array(
		array('cardid',$username),
		array('password',$password)
	);
	$item = M('parents')->where($w)->find();
	if($item){
		set_session('parents',$item);
		output('登录成功','?a=index',2);
	}
	output('登录失败,请检查身份证号码是否正确');
}

//退出
function do_logout(){
	set_session('parents',false);
	output('安全退出！','?a=index&m=login',0);
}

function page($count,$pagesize,$page,$base_url){
	$pages = ceil($count/$pagesize);
	$str = '<nav><ul class="pagination">';
    $str .= $page > 1 ? '<li><a href="'.$base_url.'&page='.($page - 1).'">&laquo;</a></li>' : '<li><a href="#">&laquo;</a></li>';
	if($page < 3){
		$start = 1;
		$end = min($pages,$start + 4);
	}elseif($pages - $page < 2){
		$end = $pages;
		$start = max(1,$end - 4);
	}else{
		$start = $page - 2;
		$end = $page + 2;
	}
	for($i = $start;$i <= $end;$i++)
	{
		$str .= $page == $i ? '<li class="active"><a href="#">'.$i.'</a></li>' : '<li><a href="'.$base_url.'&page='.$i.'">'.$i.'</a></li>' ;
	}
    $str .= $pages > $page ? '<li><a href="'.$base_url.'&page='.($page + 1).'">&raquo;</a></li>' : '<li><a href="#">&raquo;</a></li>';
	$str .= '</ul></nav>';
	return $str;
}