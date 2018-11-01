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
| Note: 游戏设置表
+----------------------------------------------------------------------
*/
class Game_playModule extends Module{
	public function __construct($table = ''){
		parent::__construct('game_play');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`vmid` CHAR(10),
				`pacode` char(4) DEFAULT '' COMMENT '货道编号',
				`goods_id` INT(11) DEFAULT 0 COMMENT '商品id',
				`goods_name` VARCHAR(20) DEFAULT '' COMMENT '商品名称',
				`game_type` int(5) COMMENT '游戏类型,1再来一瓶,2转盘抽奖',
				`win_code` int(11) DEFAULT 0 COMMENT '是否中奖，0未中奖，1再来一包，2再来二包，3再来三包,',
				`related_id` int(11) DEFAULT 0 COMMENT '再来一瓶online_order表id, 转盘抽奖game_pay表id',
				`prize_status` int(11) DEFAULT 0 COMMENT '奖品状态,0未领奖,1已领奖',
				`play_desc` varchar(2000) COMMENT '备注',
				`createtime` INT DEFAULT 0 COMMENT '创建时间',
				PRIMARY KEY (`id`),
				KEY(`vmid`),
				KEY(`createtime`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $this->excute('DROP TABLE IF EXISTS ' . $this->table);
		$this->excute($sql);

	}
}