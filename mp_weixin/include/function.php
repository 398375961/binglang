<?php
//获取 access_token
function get_access_token($cfg){
	$access_token = cache_data('token_'.$cfg['appid']);
	if(!$access_token){
		$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$cfg['appid'].'&secret='.$cfg['appsecret'];
		$res = file_get_contents($url);
		$json = json_decode($res,true);
		$access_token = $json['access_token'];
		cache_data('token_'.$cfg['appid'],$access_token,5400);
	}
	return $access_token;
}

//获取jsapi_ticket
function get_jsapi_ticket($cfg){
	$jsapi_ticket = cache_data('ticket_'.$cfg['appid']);
	if(!$jsapi_ticket){
		$access_token = get_access_token($cfg);		
		$url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
		$res = file_get_contents($url);
		$json = json_decode($res,true);
		$jsapi_ticket = $json['ticket'];
		cache_data('ticket_'.$cfg['appid'],$jsapi_ticket,5400);
	}
	return $jsapi_ticket;
}