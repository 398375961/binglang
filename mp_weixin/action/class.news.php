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
class NewsAction extends Action{
	private $weixin_mp = null;

	public function index(){
		$article_id = intval($_REQUEST['aid']);
		$article = M('weixin_article')->where(array('id',$article_id))->find();
		if($_REQUEST['key']){
			$article['link'] = $this->get('cfg.web_url').'mp_weixin/index.php?a=news&aid='.$article['id'].'&client='.$_REQUEST['client'].'&scene_id='.$_REQUEST['scene_id'].'&key='.$_REQUEST['key'];
			$article['pic'] = $this->get('cfg.web_url').'public/'.$article['pic'];
			$this->set_mp($article);
			//查询是测试，还是正常
			$where = array('id',intval($_REQUEST['scene_id']));
			$prize_cfg = M('weixin_vmid')->where($where)->find();
			$article['is_test'] = $prize_cfg['status'] == 2;
		}
		$this->set('article',$article);
		$this->tpl();
	}

	public function shared(){
		$article_id = intval($_REQUEST['aid']);
		$client = $_REQUEST['client'];
		$scene_id = intval($_REQUEST['scene_id']);
		$key = $_REQUEST['key'];
		echo '谢谢您的分享！';
		$article = M('weixin_article')->where(array('id',$article_id))->find();
		if(!$article){
			echo '礼品送出失败，文章已经被删除！';
			return;
		}
		$this->mp_info($article);
		//验证
		if($key != md5($client.$scene_id.$this->weixin_mp['appid'])){
			exit('验证错误！');
		}
		//查询 礼品配置
		$where = array('wa.id',$scene_id);
		$table = '##weixin_vmid wa JOIN ##weixin_mp wm ON wm.id=wa.weichatid';
		$fields = 'wa.*,wm.weichat_name,wm.weichat,wm.token';
		$prize_cfg = M()->table($table)->fields($fields)->where($where)->find();
		if(!$prize_cfg){
			exit('分享送礼品活动已结束！');
		}
		//检查是否已经送出过礼品了
		$clientname = md5($client.$article_id);
		$prize = false;
		if($prize_cfg['status'] == 2){ 
			$prize = true;
		}else if($prize_cfg['status'] == 1){
			//检查是否已经送出过礼品
			$where = array(
				array('weichat',$this->weixin_mp['weichat']),
				array('clientname',$clientname)
			);
			$p_log = M('prizelog')->where($where)->find();
			if(empty($p_log)){
				$prize = true;
			}
		}
		if($prize){
			if(Model('prize')->check_machine($prize_cfg['vmid'])){
				$res = Model('prize')->push_prize($prize_cfg,$clientname);
				if($res === true){
					//echo '恭喜您，获得小礼品！请在取物口取出';
					echo $prize_cfg['prize_str'];
				}else{
					echo $res;
				}
			}else{
				echo '抱歉，无法连接机器派送礼品！';
			}
		}
	}

	private function mp_info($article){
		$weichatid = intval($article['weichatid']);
		$this->weixin_mp =  M('weixin_mp')->where(array('id',$weichatid))->find();
		if(!$this->weixin_mp) exit('error :weichatid');
	}

	private function set_mp($article){
		$this->mp_info($article);
		$jsapi_ticket = get_jsapi_ticket($this->weixin_mp);
		$noncestr = substr(md5(NOW_TIME.$jsapi_ticket),8,16);
		$tmpStr  = 'jsapi_ticket='.$jsapi_ticket;
		$tmpStr .= '&noncestr='.$noncestr;
		$tmpStr .= '&timestamp='.NOW_TIME;
		$tmpStr .= '&url='.$article['link'];
		$signature = sha1($tmpStr);
		$this->weixin_mp['noncestr'] = $noncestr;
		$this->weixin_mp['signature'] = $signature;
		$this->weixin_mp['tmpStr'] = $tmpStr;
		$this->set('weixin_mp',$this->weixin_mp);
	}
}