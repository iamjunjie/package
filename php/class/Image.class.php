<?php
/**
 * 图片类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2016-03-16
 */
class Image {

	//图片信息
	private $info = null;

	/**
	 * 获取图片信息
	 * 
	 * @param  mixed  $file 图片文件
	 * @return array
	 */
	public function getInfo($file){
		if(!isset($this->info[$file])){
			$B = filesize($file); //byte
			$K = sprintf('%.2f', $B / 1024); //KB
			$M = sprintf('%.2f', $K / 1024); //MB
			$G = sprintf('%.2f', $M / 1024); //GB
			$T = sprintf('%.2f', $G / 1024); //TB
			$info = getimagesize($file);
			$this->info[$file] = array(
				'width'  => $info[0],
				'height' => $info[1],
				'ext'	 => image_type_to_extension($info[2], false),
				'mime'	 => $info['mime'],
				'size'   => array('B'=>$B, 'K'=>$K, 'M'=>$M, 'G'=>$G, 'T'=>$T),
			);
		}
		return $this->info[$file];
	}

	/**
	 * 获取扩展名
	 * 
	 * @param  string $file 文件路径
	 * @return string
	 */
	public function getExt($file){
		$info = $this->getInfo($file);
		return $info['ext'];
	}

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

	/**
	 * 上传文件
	 * 
	 * @param  string $src 临时图片
	 * @param  string $dst 目标文件
	 * @return boolean
	 */
	public function upload($src, $dst){
		return @move_uploaded_file($src, $dst);
	}

	/**
	 * 等比例压缩
	 * 
	 * @param  array  $params 参数
	 *                        src => 原图路径
	 *                        dst => 压缩图路径
	 *                        scale => 比例
	 */
	public function compress($params){
		//参数
		$src   = $params['src'];
		$dst   = $params['dst'];
		$scale = $params['scale'];
		//原始图片 宽/高
		$info  = $this->getInfo($src);
		$src_w = $info['width'];
		$src_h = $info['height'];
		//原始图片 图像标识符
		$src_fun = "imagecreatefrom{$info['ext']}";
		$src_img = $src_fun($src);
		//目标图片 宽/高
		$dst_w = $src_w / $scale;
		$dst_h = $dst_w * $src_h / $src_w;
		//目标图片 图像标识符
		$dst_img = imagecreatetruecolor($dst_w, $dst_h);
		//重采样拷贝部分图像并调整大小
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
		//目标图片 生成
		$dst_fun = "image{$info['ext']}";
		$dst_fun($dst_img, $dst);
		//销毁 图像标识符
		imagedestroy($src_img);
		imagedestroy($dst_img);
	}

	/**
	 * 加文字水印
	 * 
	 * @param  array  $params 参数
	 *                        src => 原图路径
	 *                        text => 水印文字
	 *                        font => 水印字体
	 *                        red =>  红色
	 *                        green => 绿色
	 *                        blue => 蓝色
	 *                        alpha => 透明度
	 *                        size => 文字大小
	 *                        angle => 角度
	 */
	public function textMark($params){
		//参数
		$src     = $params['src'];
		$text    = $params['text'];
		$font    = $params['font'];
		$size    = (isset($params['size']) ? $params['size'] : 12);
		$color   = (isset($params['color']) ? $params['color'] : '#000000');
		$color   = str_replace('#', '', $color);
		$red     = hexdec($color[0] . $color[1]);
		$green   = hexdec($color[2] . $color[3]);
		$blue    = hexdec($color[4] . $color[5]);
		$alpha   = (isset($params['alpha']) ? $params['alpha'] : 0);
		$angle   = (isset($params['angle']) ? $params['angle'] : 0);
		$space_x = (isset($params['space_x']) ? $params['space_x'] : 10);
		$space_y = (isset($params['space_y']) ? $params['space_y'] : 10);
		$place   = (isset($params['place']) ? $params['place'] : 0);
		//原始图片 图像标识符
		$info = $this->getInfo($src);
		$src_fun = "imagecreatefrom{$info['ext']}";
		$src_img = $src_fun($src);
		//为一幅图像分配颜色 + alpha
		$img_color = imagecolorallocatealpha($src_img, $red, $green, $blue, $alpha);
		//图片宽高
		$img_w = $info['width'];
		$img_h = $info['height'];
		//取得使用 TrueType 字体的文本的范围
		$text_box = imagettfbbox($size, $angle, $font, $text);
		//水印文字 宽/高
		$tb_w = abs($text_box[6] - $text_box[2]);
		$tb_h = abs($text_box[7] - $text_box[3]);
		$x = $y = 0;
		switch ($place) {
			case 0: //左上角
				$x = 0 + $space_x;
				$y = $tb_h + $space_y;
				break;
			case 1: //右上角
				$x = $img_w - $tb_w - $space_x;
				$y = $tb_h + $space_y;
				break;
			case 2: //左下角
				$x = 0 + $space_x;
				$y = $img_h - $space_y;
				break;
			case 3: //右下角
				$x = $img_w - $tb_w - $space_x;
				$y = $img_h - $space_y;
				break;
		}
		//用 TrueType 字体向图像写入文本
		//x，y表示第一个字符的左下角坐标
		imagettftext($src_img, $size, $angle, $x, $y, $img_color, $font, $text);
		//重新 生成 图片
		$dst_fun = "image{$info['ext']}";
		$dst_fun($src_img, $src);
		imagedestroy($src_img);
	}
}