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
class Game_configModule extends Module{
	public function __construct($table = ''){
		parent::__construct('game_config');
	}
	
	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`game_type` int(5) COMMENT '游戏类型,1幸运转盘,2转盘抽奖,3赛跑',
				`config_key` varchar(30) COMMENT '设置key',
				`val` varchar(2000) COMMENT '设置值',
				`sorting` int(11) COMMENT '排序',
				`config_desc` varchar(2000) COMMENT '设置说明',
				PRIMARY KEY (`id`),
				KEY(`config_key`)
		)ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $this->excute('DROP TABLE IF EXISTS ' . $this->table);
		$this->excute($sql);
		$this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(1, 'xyzp_rate1', '0', 10, '幸运中奖概率1包')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(1, 'xyzp_rate2', '0', 10, '幸运中奖概率2包')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(1, 'xyzp_rate3', '0', 10, '幸运中奖概率3包')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(1, 'xyzp_enable', '1', 20,  '幸运转盘是否启用')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(2, 'zpcj_rate', '0', 10, '转盘抽奖概率')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(2, 'zpcj_enable', '0', 20,  '转盘抽奖是否启用')");
        $this->excute("INSERT INTO " . $this->table . "(game_type, config_key, val, sorting, config_desc) VALUES(2, 'zpcj_price', '0', 9, '转盘抽奖价格')");
	}
}