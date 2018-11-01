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
| 商品信息api
+----------------------------------------------------------------------
*/
class GoodsAction extends Action{

	public function info(){
		$id = intval($_GET['gid']);
		$goods = M('goods')->where(['id',$id])->find();
		if(!$goods){
			echo json_encode(['status' => 0,'msg' => '商品不存在']);
			return;
		}
		$image_url = empty($goods['goods_pic']) ? '' : $this->get('cfg.web_url').$goods['goods_pic'];
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
		echo json_encode(['status' => 1,'data' => $data]);
	}

	//商品分类信息
	public function goods_types(){
		$user_id = intval($_REQUEST['uid']);
		$data = M('goods_type')->where(['user_id',$user_id])->order('parent_id','ASC')->select();
		$list = $this->get_types_sons($data,0);
		echo json_encode($list);
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

	public function goods_list(){
		$vmid = $_REQUEST['vmid'];
		$sort_id = intval($_REQUEST['sort_id']);
		if(!is_numeric($vmid) || strlen($vmid) != 10) echo '[]';
		$table = '##road r JOIN ##goods g ON g.id=r.goods_id';
		$where = [
			['r.vmid',$vmid]
		];
		if($sort_id) $where[] = ['g.goods_type',$sort_id];
		$fields = 'r.pacode,g.goods_name,g.goods_guige AS danwei,g.id,g.goods_price AS price,r.price AS price2, g.goods_pic,g.goods_desc';
		$list = M()->table($table)->fields($fields)->where($where)->select();
		echo json_encode($list);
	}
}