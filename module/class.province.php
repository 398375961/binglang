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
class ProvinceModule extends Module{
	private $citys = array();

	public function __construct($table = ''){
		parent::__construct('province');
	}

	public function getname($id){
		if(empty($this->citys[$id])){
			$this->citys[$id] = $this->where(array('provinceid',$id))->find();
		}
		return $this->citys[$id]['provincename'];
	}

	//初始化表，请慎用
	public function create_table(){
		$sql = 'CREATE TABLE '.$this->table."(
				provinceid INT NOT NULL PRIMARY KEY ,
				provincename VARCHAR( 50 ) NULL
			)ENGINE=MyISAM DEFAULT CHARSET=utf8";
		$this->excute('DROP TABLE IF EXISTS '.$this->table);
		$this->excute($sql);
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (1, '北京市')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (2, '天津市')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (3, '河北省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (4, '山西省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (5, '内蒙古自治区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (6, '辽宁省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (7, '吉林省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (8, '黑龙江省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (9, '上海市')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (10, '江苏省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (11, '浙江省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (12, '安徽省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (13, '福建省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (14, '江西省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (15, '山东省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (16, '河南省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (17, '湖北省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (18, '湖南省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (19, '广东省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (20, '广西壮族自治区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (21, '海南省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (22, '重庆市')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (23, '四川省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (24, '贵州省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (25, '云南省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (26, '西藏自治区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (27, '陕西省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (28, '甘肃省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (29, '青海省')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (30, '宁夏回族自治区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (31, '新疆维吾尔自治区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (32, '香港特别行政区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (33, '澳门特别行政区')");
		$this->excute('INSERT INTO '.$this->table." (provinceid, provincename) VALUES (34, '台湾省')");
	}
}