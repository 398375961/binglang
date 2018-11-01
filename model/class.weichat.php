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
+----------------------------------------------------------------------
*/
class WeichatModel{ 
	private $data = array();

	//设置token
	public function init($token){ 
		$this->auth($token) or exit('error'); 
		if($_GET['echostr']){ 
			echo($_GET['echostr']);
			exit; 
		} else { 
			$xml = file_get_contents("php://input");
			$xml = new SimpleXMLElement($xml); 
			$xml || exit; 
			foreach ($xml as $key => $value) {
				$this->data[$key] = strval($value); 
			} 
		} 
	} 
	
	public function request(){ 
		return $this->data; 
	} 
	
	public function response($content, $type = 'text', $flag = 0){ 
		$this->data = array( 
			'ToUserName' => $this->data['FromUserName'], 
			'FromUserName' => $this->data['ToUserName'], 
			'CreateTime' => NOW_TIME, 
			'MsgType' => $type
		); 
		$this->$type($content); 
		$this->data['FuncFlag'] = $flag;
		$xml = new SimpleXMLElement('<xml></xml>');
		$this->data2xml($xml, $this->data); 
		exit($xml->asXML()); 
	} 
	
	private function text($content){ 
		$this->data['Content'] = $content; 
	} 
	
	private function music($music){ 
		list( $music['Title'], $music['Description'], $music['MusicUrl'], $music['HQMusicUrl'] ) = $music; 
		$this->data['Music'] = $music; 
	} 
	
	private function news($news){ 
		$articles = array(); 
		foreach ($news as $key => $value) { 
			list( $articles[$key]['Title'], $articles[$key]['Description'], $articles[$key]['PicUrl'], $articles[$key]['Url'] ) = $value; if($key >= 9) { break; } 
		} 
		$this->data['ArticleCount'] = count($articles); 
		$this->data['Articles'] = $articles; 
	} 
	

	private function data2xml($xml, $data, $item = 'item') { 
		foreach ($data as $key => $value) { 
			is_numeric($key) && $key = $item; 
			if(is_array($value) || is_object($value)){ 
				$child = $xml->addChild($key); 
				$this->data2xml($child, $value, $item); 
			} else { 
				if(is_numeric($value)){ 
					$child = $xml->addChild($key, $value); 
				} else { 
					$child = $xml->addChild($key); 
					$node = dom_import_simplexml($child); $node->appendChild($node->ownerDocument->createCDATASection($value));
				} 
			} 
		} 
	} 
	
	//验证
	private function auth($token){ 
		$signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
		$tmpArr = array($token,$timestamp,$nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if($tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
}