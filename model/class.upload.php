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
| 文件上传
+----------------------------------------------------------------------
*/
class UploadModel{
	private $path = '';
	private $error = '';

	//文件存储路径，相对于public/upload/
	public function set_path($path){
		$this->path = ROOT_PWC.'public/upload/'.$path;
	}
	
	//获取错误信息
	public function get_error(){
		return $this->error;
	}

	/*
	* 传入表单名称
	* @return 相对于public的文件路径
	* 处理文件上传，请先调用 set_path() 设置好文件存储路径
	*/
	public function upload($input_name,$tails = array(),$filename = ''){
		$this->error = '';
		$file = $_FILES[$input_name];
		if(!$file || empty($file['name'])) return '';
		if($file['error']){
			$this->error = $file['error'];
			return false;
		}
		$tail = get_file_sufix($file['name']);
		if(count($tails) > 0 && (!in_array($tail,$tails))){
			$this->error = '文件类型错误！';
			return false;
		}
		if($file['size'] > 512*1024){
			$this->error = '文件太大，最大传512K！';
			return false;
		}
		if(empty($filename)){
			$filename = $this->path.date('Ym').'/'.md5(NOW_TIME.$file['name']).'.'.$tail;
		}else{
			$filename = $this->path.$filename;
		}
		$data = f_read($file['tmp_name']);
		f_write($filename,$data); //保存图片到本地
		return str_replace(ROOT_PWC.'public/','',$filename);
	}
}