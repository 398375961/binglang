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
| Note: 在线支付订单表，支付宝，微信支付等
+----------------------------------------------------------------------
*/
class Online_orderModule extends Module{
	public function __construct($table = ''){
		parent::__construct('online_order');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				id INT(12) NOT NULL AUTO_INCREMENT COMMENT '自增订单号',
				out_trade_no VARCHAR(40) COMMENT '系统唯一订单号Ymd + id后5位',
				trade_no VARCHAR(40) COMMENT '支付宝交易号',
				dynamic_id VARCHAR(40) COMMENT '条码，动态验证码',
				trade_type VARCHAR(15) COMMENT 'alipay,weichat等',
				saleid VARCHAR(12) COMMENT '机器交易号',
				vmid CHAR(10) COMMENT '机器id',
				pacode char(3) COMMENT '3位的货道ID',
				goods_id INT(10) COMMENT '商品id',
				goods_name VARCHAR(60) COMMENT '商品名称',
				amount INT(5) DEFAULT 0 COMMENT '总金额',
				pay_status INT(3) DEFAULT 0 COMMENT '支付状态(0未支付,1已支付)',
				trade_status INT(3) DEFAULT 0 COMMENT '交易状态(0未发货,1已发货，交易成功)',
				buyer_id VARCHAR(40) COMMENT '购买者id，如支付宝id',
				buyer_name VARCHAR(40) COMMENT '购买者姓名，如支付宝账号',
				note VARCHAR(400) COMMENT '备注说明',
				saletime INT(12) DEFAULT 0 COMMENT '机器交易时间',
				createtime INT(12) DEFAULT 0 COMMENT '交易创建时间',
				paytime INT(12) DEFAULT 0 COMMENT '支付时间',
				finishtime INT(12) DEFAULT 0 COMMENT '完成时间',
				user_id INT(12) DEFAULT 0 COMMENT '记账用户id',
				income_userid INT(12) DEFAULT 0 COMMENT '收款用户id',
				PRIMARY KEY (`id`),
				KEY (`vmid`),
				KEY (`buyer_name`),
				KEY (`trade_no`),
				KEY (`out_trade_no`),
				KEY (`trade_type`),
				KEY (`pay_status`),
				KEY (`trade_status`),
				KEY (`dynamic_id`),
				KEY (`income_userid`),
				KEY (`user_id`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
	}

	public function add($data = array()){
		$order_id = parent::add($data);
		if(is_numeric($order_id) && $order_id > 0 && empty($data['out_trade_no'])){
			$out_trade_no = date('Ymd');
			$tail = $order_id + 1000000;
			$out_trade_no .= substr($tail,-6);
			$out_trade_no .= rand(100,999);
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