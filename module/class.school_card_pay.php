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
| Note: 一卡通在线支付订单表
+----------------------------------------------------------------------
*/
class School_card_payModule extends Module{
	public function __construct($table = ''){
		parent::__construct('school_card_pay');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`out_trade_no` varchar(32) DEFAULT '' COMMENT '交易订单号',
				`trade_no` varchar(32) DEFAULT '' COMMENT '第三方支付订单号',
				`trade_type` tinyint(4) DEFAULT '0' COMMENT '0 现金 1 支付宝 2 微信',
				`income_userid` int(11) DEFAULT '0' COMMENT '收款人id，如果<>user_id 则表示由平台代收了',
				`user_id` int(11) DEFAULT '0' COMMENT '订单所属用户',
				`cardid` varchar(20) DEFAULT '' COMMENT '校园卡卡号',
				`amount` int(10) DEFAULT '0' COMMENT '充值金额，单位为分',
				`vmid` CHAR(10) DEFAULT '' COMMENT '是在哪台机器上充的值',
				`pay_status` tinyint(4) DEFAULT '0' COMMENT '是否已付款',
				`createtime` int(11) DEFAULT '0',
				`buyer_id` varchar(50) DEFAULT '',
				`buyer_name` varchar(50) DEFAULT '',
				`paytime` int(11) DEFAULT '0',
				`note` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`),
				KEY `trade_type` (`trade_type`),
				KEY `pay_status` (`pay_status`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='校园卡充值记录表'";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}

	public function add($data = array()){
		$order_id = parent::add($data);
		if(is_numeric($order_id) && $order_id > 0 && empty($data['out_trade_no'])){
			$out_trade_no = 'SD'.date('Ymd');
			$tail = $order_id + 1000000;
			$out_trade_no .= substr($tail,-6);
			$this->where(array('id',$order_id))->save(array('out_trade_no' => $out_trade_no));
		}
		return $order_id;
	}

	//获取商户订单号
	public function get_out_trade_no($order_id = 0){
		$order = $this->fields('out_trade_no')->where(array('id',intval($order_id)))->find();
		return $order['out_trade_no'];
	}
}