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
| Note: 取货记录表
+----------------------------------------------------------------------
*/
class QuhuoModule extends Module{
	public function __construct($table = ''){
		parent::__construct('quhuo');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增唯一id',
				`vmid` char(10) NOT NULL DEFAULT '',
				`pacode` char(3) NOT NULL DEFAULT '',
				`salecode` varchar(32) NOT NULL DEFAULT '' COMMENT '取货码',
				`goods_id` int(11) DEFAULT '0',
				`goods_name` varchar(255) DEFAULT '',
				`createtime` int(11) DEFAULT '0' COMMENT '创建时间',
				`saletime` int(11) DEFAULT '0' COMMENT '出货时间',
				`sale_status` tinyint(4) DEFAULT '0' COMMENT '出货状态 0 未出货 1已出货',
				`callbackurl` varchar(255) DEFAULT '' COMMENT '回调地址，出货完成后回调该地址',
				`note` varchar(255) DEFAULT '' COMMENT '备注',
				PRIMARY KEY (`id`),
				UNIQUE KEY `vmid` (`vmid`,`salecode`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商城取货记录表';";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}
}