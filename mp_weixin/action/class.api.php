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
class ApiAction extends Action{
	private $weixin_mp = null;

	protected function before(){
		$token = $_REQUEST['token'];
		//根据token 查询公众号
		$weixin =	M('weixin_mp')->where(array('token',$token))->find();
		if(!$weixin) exit('error token:'.$token);
		$this->weixin_mp = $weixin;
	}
	
	//获取access_token
	public function get_access_token(){
		$access_token = get_access_token($this->weixin_mp);
		echo $access_token;
	}

	//创建关注二维码
	public function create_subscribe_qr(){
		$scene_id = intval($_REQUEST['scene_id']);
		$key = 'subscribe_qr_ticket_'.$this->weixin_mp['appid'].'_'.$scene_id;
		$data = cache_data($key);
		if(!$data){
			$access_token = get_access_token($this->weixin_mp);
			$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
			$str_post = '{"expire_seconds":2592000,"action_name":"QR_SCENE","action_info":{"scene":{"scene_id":'.$scene_id.'}}}';
			$s = curl_init(); 
			curl_setopt($s,CURLOPT_URL,$url);  
			curl_setopt($s,CURLOPT_TIMEOUT,15);  
			curl_setopt($s,CURLOPT_RETURNTRANSFER,true);  
			curl_setopt($s,CURLOPT_POST,true); 
			curl_setopt($s,CURLOPT_POSTFIELDS,$str_post); 
			$res = curl_exec($s);
			$json = json_decode($res,true);
			if($json['errcode']){
				echo $res; //输出错误
				return;
			}
			$data['ticket'] = $json['ticket'];
			$data['create'] = NOW_TIME;
			$data['expire'] = 2592000;
			cache_data($key,$data,2592000);
		}
		echo '<img src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$data['ticket'].'" width="200"/>';
		echo '<br/>';
		echo '二维码生成时间：'.date('Y-m-d H:i:s',$data['create']);
		echo '<br/>';
		echo '二维码有效期至：'.date('Y-m-d H:i:s',$data['create'] + $data['expire']);
	}
}