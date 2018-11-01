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
	private $weichat = null;
	private $token = '';
	private $weixin_mp = null;
	private $data;
	private $debug = false;
	private $scene_id = 0; //二维码关注 场景id

	protected function before(){
		$this->token = trim($_GET['token']);
		$this->weichat = Model('weichat');
		$this->weichat->init($this->token);
		//根据token 查询公众号
		$weixin = M('weixin_mp')->where(array('token',$this->token))->find();
		if(!$weixin) exit('error token:'.$this->token);
		$this->weixin_mp = $weixin;
	}


	public function index(){
		$this->data = $this->weichat->request();
		switch($this->data['MsgType']){
			case 'text':
				//文本消息
				$key = trim($this->data['Content']);
				$this->do_response($key);
			break;
			case 'event':
				$this->do_event();
			break;
		}
	}

	private function do_event(){
		switch($this->data['Event']){
			case 'CLICK':
				//自定义菜单，点击事件
				$key = $this->data['EventKey'];
				$this->do_response($key);
			break;
			case 'VIEW':
				$url = $this->data['EventKey'];
				$this->weichat->response('浏览了网页：'.$url,'text');
			break;
			case 'subscribe':
				//关注时
				if($this->data['EventKey']){
					//带事件推送的关注
					if(substr($this->data['EventKey'],0,7) == 'qrscene'){
						$this->scene_id = intval(substr($this->data['EventKey'],8));
					}
				}
				$this->do_response('subscribe');
			break;
			case 'unsubscribe':
				//取消订阅
			break;
			case 'SCAN':
				//二维码关注事件推送
				$this->w_log(var_export($this->data,true));
				$this->scene_id = intval($this->data['EventKey']);
				$this->do_response('subscribe');
			break;
		}
	}

	//关键词自动回复
	private function do_response($key){
		$where = array(
			array('weichatid',$this->weixin_mp['id']),
			array('auto_key',$key),
		);
		$auto = M('weixin_auto')->where($where)->find();
		if(!$auto){
			$this->weichat->response($this->weixin_mp['default_msg'],'text');
			return;
		}
		if($this->scene_id){
			$where = array('wa.id',$this->scene_id);
			$table = '##weixin_vmid wa JOIN ##weixin_mp wm ON wm.id=wa.weichatid';
			$fields = 'wa.*,wm.weichat_name,wm.weichat,wm.token';
			$prize_cfg = M()->table($table)->fields($fields)->where($where)->find();
		}
		switch($auto['prizetype']){
			case 'prize':
				$prize = false;
				if($this->scene_id){
					//检查是否可以送礼品
					if($prize_cfg['status'] == 2){ 
						$prize = true;
						//如果是3秒内有礼品送出，此次不送出；微信有重复调用的情况
						$where = array(
							array('weichat',$this->weixin_mp['weichat']),
							array('clientname',$this->data['FromUserName']),
							array('prizetime',NOW_TIME - 3,'>')
						);
						$p_log = M('prizelog')->where($where)->find();
						if($p_log){
							$prize = false;
							$this->w_log('repeat!');
						}
					}else if($prize_cfg['status'] == 1){
						//检查是否已经送出过礼品
						$where = array(
							array('weichat',$this->weixin_mp['weichat']),
							array('clientname',$this->data['FromUserName'])
						);
						$p_log = M('prizelog')->where($where)->find();
						if(empty($p_log)){
							$prize = true;
						}
					}
				}
				if($prize){
					//关注微信后直接送出礼品，无需验证码
					if(Model('prize')->check_machine($prize_cfg['vmid'])){
						$res = Model('prize')->push_prize($prize_cfg,$this->data['FromUserName']);
						if($res === true){
							$auto['auto_content'] .= $prize_cfg['prize_str'];
						}else{
							$auto['auto_content'] .= chr(13).$res;
						}
					}else{
						$auto['auto_content'] .= chr(13).'无法连接机器派送礼品，请稍后再试！';
					}
				}
				$this->weichat->response($auto['auto_content'],'text');
			break;
			case 'randcode':
				if($this->scene_id){
					$prize = false;
					//检查是否可以送礼品
					if($prize_cfg['status'] == 2){ 
						$prize = true;
					}else if($prize_cfg['status'] == 1){
						//检查是否已经送出过礼品
						$where = array(
							array('weichat',$this->weixin_mp['weichat']),
							array('clientname',$this->data['FromUserName'])
						);
						$p_log = M('prizelog')->where($where)->find();
						if(empty($p_log)){
							$prize = true;
						}
					}
					if($prize){
						//需在机器上输入验证码，随机验证码送礼品
						$text = $auto['auto_content'];
						$text = str_replace('#randcode#',$this->create_rand(),$text);
						$this->weichat->response($text,'text');
						break;
					}
				}
				$this->weichat->response($this->weixin_mp['default_msg'],'text');
			break;
			case 'share':
				//分享文章送礼品
				$article = M('weixin_article')->where(array('id',intval($auto['articleid'])))->find();
				if(!$article){
					$this->weichat->response($this->weixin_mp['default_msg'],'text');
					break;
				}
				$news = array();
				$url = $this->get('cfg.web_url').'mp_weixin/index.php?a=news&aid='.$article['id'].'&client='.$this->data['FromUserName'].'&scene_id='.$this->scene_id.'&key='.md5($this->data['FromUserName'].$this->scene_id.$this->weixin_mp['appid']);
				$news[] = array($article['title'],$auto['auto_content'],$this->get('cfg.web_url').'public/'.$article['pic'],$url);
				$this->weichat->response($news,'news');
			break;
			default:
				//普通消息，自动回复
				$article = M('weixin_article')->where(array('id',intval($auto['articleid'])))->find();
				if(!$article){
					$this->weichat->response($auto['auto_content'],'text');
					break;
				}
				$news = array();
				$url = $this->get('cfg.web_url').'mp_weixin/index.php?a=news&aid='.$article['id'];
				$news[] = array($article['title'],$auto['auto_content'],$this->get('cfg.web_url').'public/'.$article['pic'],$url);
				$this->weichat->response($news,'news');
			break;
		}
	}

	private function create_rand(){
		$data = array(
			'weichat'		=> $this->weixin_mp['weichat'],
			'scene_id'		=> $this->scene_id,
			'client'		=> $this->data['FromUserName'],
			'createtime'	=> NOW_TIME,
			'has_prize'		=> 0,
		);
		while(true){
			$code = rand(100000,999899);
			$where = array(
				array('rand_code',$code,'>'),
				array('rand_code',$code + 100,'<')
			);
			$codes = array();
			$res = M('rand_code')->fields('rand_code')->where($where)->select();
			foreach($res as $a) $codes[] = $a['rand_code'];
			for($i = 1; $i < 100; $i++){
				$code++;
				if(!in_array($code,$codes)){
					$data['rand_code'] = $code;
					M('rand_code')->add($data);
					return $code;
				}
			}
		}
		if(rand(1,100) < 10){
			//清除那些过期，没用到的验证码 ,清除10天以前的
			$where = array('createtime',NOW_TIME - 864000,'<');
			M('rand_code')->where($where)->delete();
		}
	}

	private function w_log($str){
		if(!$this->debug) return;
		$file = PATH_CACHE.'mplog.txt';
		$str = date('Ymd H:i:s ').microtime(true).' '.$str.chr(10).chr(13);
		f_write($file,$str,'a');
	}
	
}