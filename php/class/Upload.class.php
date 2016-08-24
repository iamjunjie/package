<?php
/**
 * 文件上传类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-06-15
 */
class Upload {

	/**
	 * 上传文件
	 */
	public function uploadFile(){
		if(!isset($_FILES['file']) || empty($_FILES['file'])){
			$this->response(false, '请选择上传文件');
		}
		//上传文件信息
		$files = $_FILES['file'];
		//上传类型
		$type = $this->post('type', 'image');
		//允许格式
		$exts = $this->post('exts', array('not set'));
		$exts = explode('|', $exts);
		//检测格式
		$ext = strtolower('.' . pathinfo($files['name'], PATHINFO_EXTENSION));
		if(!in_array($ext, $exts)){
			$this->response(false, '文件格式不正确');
		}
		//检测大小
		$size = $this->post('size', 2);
		if($files['size'] > ($size*pow(1024, 2))){
			$this->response(false, '文件太大');
		}
		//上传文件
		$path  = dirname(__FILE__);
		$path .= (DS . 'uploads' . DS . $type . DS . date('Ymd') . DS);
		if(!$this->mkdir($path)){
			$this->response(false, '目录生成失败', $path);
		}
		$path .= md5(uniqid() . time()) . $ext;
		if(move_uploaded_file($files['tmp_name'], $path)){
			$host   = 'http://' . $_SERVER['HTTP_HOST'];
			$search = dirname(dirname(__FILE__));
			$path   = str_replace($search, $host, $path);
			$path   = str_replace('\\', '/', $path);
			$this->response(true, '上传成功', $path);	
		}
		$this->response(false, '上传失败', $path);
	}
	
	/**
	 * 获取POST参数
	 * 
	 * @param  string $name    表单名
	 * @param  mixed  $default 默认返回值
	 * @return mixed
	 */
	private function post($name, $default = ''){
		if(isset($_POST[$name])){
			return $_POST[$name];
		}
		return $default;
	}

	/**
	 * 数据相应
	 * 
	 * @param  boolean $success true成功，false失败
	 * @param  string  $msg     错误消息
	 * @param  mixed   $data    返回数据
	 */
	private function response($success, $msg, $data = ''){
		exit(json_encode(array('success' => $success, 'msg' => $msg, 'data' => $data)));
	}

	/**
	 * 创建目录
	 * 
	 * @param  string $path 目录
	 * @return boolean true成功，false失败
	 */
	private function mkdir($path){
		if(empty($path) || file_exists($path)){ return true; }
		$paths = explode(DS, trim($path, DS));
		//linux系统临时目录以'/'开头
		$tmpPath = ((DS==='/') ? DS : '');
		foreach ($paths as $value) {
			$tmpPath .= ($value . DS);
			//目录存在，切换，继续
			if(file_exists($tmpPath) && @chdir($tmpPath)){ continue; }
			//目录不存在，创建，授权，切换，继续
			if(!(@mkdir($tmpPath) && @chmod($tmpPath, 0775) && @chdir($tmpPath))){ return false; }
		}
		return true;
	}
}

//目录分隔符
define('DS', DIRECTORY_SEPARATOR);
//上传文件
$upload = new Upload();
$upload->uploadFile();