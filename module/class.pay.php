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
| Note: 收支明细表
+----------------------------------------------------------------------
*/
class PayModule extends Module{
	public function __construct($table = ''){
		parent::__construct('pay');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE `'.$this->table."`(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`vmid` CHAR(10),
				`currency` TINYINT(1) COMMENT '0：硬币1：纸币2: 普通卡3：学生卡',
				`amount` INT(6) COMMENT '面值',
				`time`	INT(12) DEFAULT 0 COMMENT '销售时间',
				`payments` TINYINT(1) COMMENT '0：收币1：找零2：吞币',
				`num`	INT(3) COMMENT '数量',
				`saleid`	VARCHAR(12) COMMENT '交易号',
				`card` VARCHAR(20) COMMENT '卡号',
				`coinchannel`	VARCHAR(1),
				`createtime`	INT(12) DEFAULT 0 COMMENT '传输时间',
				`saledate` INT(8) DEFAULT 0 COMMENT '销售日期',
				`createdate` INT(8) DEFAULT 0 COMMENT '传输日期',
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
}