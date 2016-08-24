<?php
/**
 * HTML字符串处理类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2016-01-26
 */
class Html {

	/**
	 * curl获取url的html字符串
	 * 
	 * @param  string $url url地址
	 * @return stirng
	 */
	public function getHtmlByCurl($url){
		$ch = curl_init($url);
		//禁用后cURL将终止从服务端进行验证
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//将获取的信息以文件流的形式返回，而不是直接输出
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//启用时会将头文件的信息作为数据流输出
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//在HTTP请求中包含一个"User-Agent: "头的字符串
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		//执行请求
		$content = curl_exec($ch);
		//关闭
		curl_close($ch);
		return $this->trimBom($content);
	}

	/**
	 * 过滤字符串中的BOM
	 * 
	 * @param  string $string 字符串
	 * @return string
	 */
	public function trimBom($string){
		$chars[1] = substr($string, 0, 1);
		$chars[2] = substr($string, 1, 1);
		$chars[3] = substr($string, 2, 1);
		if (ord($chars[1]) == 239 && ord($chars[2]) == 187 && ord($chars[3]) == 191) {
			$string = substr($string, 3);
		}
		return $string;
	}
}