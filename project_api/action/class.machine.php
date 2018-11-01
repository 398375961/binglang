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
| 留言箱,售书机留言 api
+----------------------------------------------------------------------
*/
class MachineAction extends Action{
	
	const C_OK = '1';
	const C_ERROR_1 = '参数错误';
	const C_ERROR_2 = '缺少参数';
	const C_ERROR_3 = '签名错误';
	const C_ERROR_4 = '未找到机器';
	const C_ERROR_5 = '系统错误，请重试';
	
	private $vmid;
	private $machine;
	
	protected function before(){
		$this->vmid = $_REQUEST['vmid'];
		if(empty($this->vmid)){
			$this->result(self::C_ERROR_2);
		}
		if(!is_numeric($this->vmid) || strlen($this->vmid) != 10){
			$this->result(self::C_ERROR_1);
		} 
		$this->machine = M('machine')->where(array('vmid',$this->vmid))->find();
		if(!is_array($this->machine)){
			$this->result(self::C_ERROR_4);
		}
		$this->verify();
	}
	
	//售书机 机器留言
	public function message(){
		$data = [
			'vmid'			=> $this->vmid,
			'username'		=> $_POST['username'],
			'email'			=> $_POST['email'],
			'mobile'		=> $_POST['mobile'],
			'qq'			=> $_POST['qq'],
			'message'		=> $_POST['content'],
			'createtime'	=> NOW_TIME
		];
		if(empty($_POST['content'])) $this->result('留言内容不能为空！');
		$res = M('messagebox')->add($data);
		if($res) $this->result(self::C_OK);
	}

	//商品详情
	public function goods_info(){
		$id = intval($_GET['gid']);
		$goods = M('goods')->where(['id',$id])->find();
		if(!$goods){
			$this->result('商品不存在');
		}
		$image_url = empty($goods['goods_pic']) ? '' : $this->get('cfg.web_url').'public/'.$goods['goods_pic'];
		$data = [
			'gid'		=> $goods['id'],
			'gname'		=> $goods['goods_name'],
			'image'		=> $image_url,
			'sort_id'	=> $goods['goods_type'],
			'desc'		=> $goods['goods_desc']
		];
		if($goods['goods_type']){
			$sort = M('goods_type')->where(['id',$goods['goods_type']])->find();
			$data['sort_name'] = $sort['type_name']; 
		}
		$this->result(self::C_OK,$data);
	}

	//商品分类信息
	public function goods_types(){
		$user_id = $this->machine['user_id'];
		$data = M('goods_type')->where(['user_id',$user_id])->order('parent_id','ASC')->select();
		$list = $this->get_types_sons($data,0);
		$this->result(self::C_OK,$list);
	}
	private function get_types_sons($data,$pid){
		$list = [];
		foreach($data as $item){
			if($item['parent_id'] == $pid){
				$sons = $this->get_types_sons($data,$item['id']);
				$list[] = [
						'id'		=> $item['id'],
						'sort_name'	=> $item['type_name'],
						'note'		=> $item['note'],
						'sons'		=> $sons
					];
			}
		}
		return $list;
	}

	//商品列表
	public function goods_list(){
		$sort_id = intval($_REQUEST['sort_id']);
		$table = '##road r JOIN ##goods g ON g.id=r.goods_id';
		$where = [
			['r.vmid',$this->vmid]
		];
		if($sort_id) $where[] = ['g.goods_type',$sort_id];
		$fields = 'r.pacode,g.goods_name,g.goods_guige AS danwei,g.id,g.goods_price AS price,r.price AS price2, g.goods_pic,g.goods_desc';
		$list = M()->table($table)->fields($fields)->where($where)->select();
		$this->result(self::C_OK,$list);
	}

	/*
	* $parms 除去sign以外的所有参数
	* return sign 签名
	*/
	private function sign($parms){
		ksort($parms);
		$raw_str = implode('',$parms);
		$sign = strtolower(md5($raw_str));
		return md5($sign.$this->machine['pwd']);
	}

	//签名验证
	private function verify(){
		$sign = $_REQUEST['sign'];
		if(empty($sign)) $this->result(self::C_ERROR_2);
		unset($_REQUEST['sign']);
		$sign_2 = $this->sign($_REQUEST);
		if($sign != $sign_2) $this->result(self::C_ERROR_3);
	}

	//返回结果
	private function result($code,$data = []){
		echo json_encode(array('code' => $code,'result' => $data));
		exit;
	}
}