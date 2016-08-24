<?php
/**
 * 目录类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-04-28
 */
class Dir {
	
	/**
	 * 创建目录
	 * 
	 * @param  string $path 目录路径
	 * @return boolean true成功，false失败 
	 */
	public function mkdir($path){
		if(empty($path) || is_dir($path)){ return true; }
		$ds = DIRECTORY_SEPARATOR;
		$paths = explode($ds, trim($path, $ds));
		//linux系统临时目录以'/'开头
		$tmp_path = (($ds === '/') ? $ds : '');
		foreach ($paths as $value) {
			$tmp_path .= ($value . $ds);
			//目录存在，切换，继续
			if(is_dir($tmp_path) && @chdir($tmp_path)){ continue; }
			//目录不存在，创建，授权，切换，继续
			if(!(@mkdir($tmp_path) && @chmod($tmp_path, 0775) && @chdir($tmp_path))){ return false; }
		}
		return true;
	}
}