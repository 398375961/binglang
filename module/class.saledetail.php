<?PHP
/*
+----------------------------------------------------------------------
| SPF-简单的PHP框架 1.0 测试版
+----------------------------------------------------------------------
| Copyright (c) 2012-2016 http:528918.com All rights reserved.
+----------------------------------------------------------------------
| Licensed ( http:www.apache.org/licenses/LICENSE-2.0 )
+----------------------------------------------------------------------
| Author: lufeng <lufengreat@163.com>
+----------------------------------------------------------------------
| Note: class.base_action 所有Action类的基类
+----------------------------------------------------------------------
*/
class SaledetailModule extends Module{
	public function __construct($table = ''){
		parent::__construct('saledetail');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."`(
				`id` INT(12) NOT NULL AUTO_INCREMENT,
				`saleid` CHAR(10) COMMENT '交易号',
				`saletype` INT(3) COMMENT '销售类型',
				`salecard` VARCHAR(32) COMMENT '卡号',
				`salemoney` INT(8) COMMENT '销售金额,单位为分',
				`saletime` INT(12),
				`salenum` TINYINT(2),
				`pacode` VARCHAR(4) COMMENT '货道',
				`vmid`	VARCHAR(20) COMMENT '机器id',
				`createtime` INT(12) COMMENT '传输时间',
				`saledate` INT(8) COMMENT '销售日期YYYYmmdd',
				`createdate` INT(8) DEFAULT 0 COMMENT '传输日期',
				`goods_id` INT(10) COMMENT '商品id',
				`goods_name` VARCHAR(20) COMMENT '商品名称',
				`price_cb` INT(8) COMMENT '成本价,单位为分',
				PRIMARY KEY (`id`),
				KEY (`vmid`),
				KEY (`createdate`),
				KEY (`saleid`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}

	//重写add操作，自动备份
	public function add($data = array()){
		$sql = 'INSERT INTO '.$this->table.$this->db->build_sql_val($data);
		$id = $this->db->insert($sql);
		if($id > 0){
			$table = $this->table.date('Ym');
			$sql = str_replace($this->table,$table,$sql);
			$id2 = $this->db->insert($sql);
			if(!$this->db->success()){
				//很有可能是表不存在
				$sql_c = 'CREATE TABLE IF NOT EXISTS '.$table.' LIKE '.$this->table;
				$this->db->excute($sql_c);
				$id2 = $this->db->insert($sql);
			}
		}
		return $id;
	}

	//重载保存方法
	public function save($data = array()){
		$sql = 'UPDATE '.$this->table.' SET '.$this->db->build_sql_val($data,true).$this->where;
		$res = $this->db->excute($sql);
		if($res){
			$sql = str_replace($this->table,$this->table.date('Ym'),$sql);
			$this->db->excute($sql);
		}
		$this->init();
		return $res;
	}
}