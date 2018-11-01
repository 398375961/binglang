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
| Note: 机器表
+----------------------------------------------------------------------
*/
class MachineModule extends Module{

	private $machines = array();

	public function __construct($table = ''){
		parent::__construct('machine');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` INT(10) NOT NULL AUTO_INCREMENT,
				`vmid` CHAR(10) NOT NULL COMMENT '机器id',
				`pwd` VARCHAR(20) NOT NULL COMMENT '网关密码',
				`bu_pwd` VARCHAR(20) NOT NULL COMMENT '补货密码',
				`vmcode` VARCHAR(10) COMMENT '机器编码',
				`vmname` VARCHAR(30) COMMENT '机器名称',
				`provinceid` INT(10) DEFAULT 0 COMMENT '省份',
				`cityid` INT(10) DEFAULT 0 COMMENT '城市',
				`districtid` INT(10) DEFAULT 0 COMMENT '县级地区',
				`address` VARCHAR(80) COMMENT '摆放地址',
				`xh` VARCHAR(20) COMMENT '机器型号',
				`client_id` VARCHAR(10) COMMENT '客户编号,客户姓名',
				`out_date` VARCHAR(10) COMMENT '出厂日期',
				`order_id` VARCHAR(20) COMMENT '订单编号',
				`ras` VARCHAR(2) COMMENT '锁机',
				`ip` VARCHAR(16) DEFAULT '' COMMENT 'ip地址',
				`lng` VARCHAR(20) DEFAULT '' COMMENT '经度',
				`lat` VARCHAR(20) DEFAULT '' COMMENT '纬度',
				`user_id` INT(8) DEFAULT 0,
				`typeid` SMALLINT(4) DEFAULT 0 COMMENT '机器类别',
				`dtu_id` VARCHAR(8) DEFAULT '' COMMENT 'DTU编号',
				`gate` VARCHAR(40) DEFAULT '' COMMENT '网关ip端口',
				`group_id` INT(10) DEFAULT 0 COMMENT '机器分组',
				`empty_buy` TINYINT(2) DEFAULT 1 COMMENT '是否允许0库存购买，主要针对酒店机H5支付',
				`private_key` VARCHAR(40) DEFAULT '' COMMENT 'api私钥',
				PRIMARY KEY (`id`),
				KEY(`vmid`),
				KEY `vmcode` (`vmcode`),
				KEY `user_id` (`user_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}

	//根据机器编码 获取机器ID
	public function get_vmid($value,$type = 'vmcode'){
		$machine = $this->fields('vmid')->where(array($type,$value))->find();
		return $machine['vmid'];
	}

	//根据机器id获取机器信息
	public function get_machine($vmid){
		if(!is_array($this->machines[$vmid])){
			$this->machines[$vmid] = $this->where(array('vmid',$vmid))->find();
		}
		return $this->machines[$vmid];
	}
	
	//获取机器编码
	public function get_vmcode($vmid){
		$m = $this->get_machine($vmid);
		return $m['vmcode'];
	}
}