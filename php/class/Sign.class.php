<?php
/**
 * 数据签名算法
 *
 * @author wangjunjie <1252547929@qq.com>
 * @date 2016-03-29
 */
class Sign {

	/**
	 * 获取数据签名
	 * 
	 * @param  array  $param 	 签名数组
	 * @param  string $code      安全校验码
	 * @param  string $sign_type 签名类型
	 * @return string       	 签名字符串
	 */
	public static function getSign($param, $code, $sign_type = 'MD5'){
		//json字符串
		$param_str = json_encode($param);
		//把拼接后的字符串再与安全校验码直接连接起来
		$param_str = $param_str . $code;
		//创建签名字符串
		return self::createSign($param_str, $sign_type);
	}
	
	/**
	 * 校验数据签名
	 *
	 * @param  string $sign 	 接口收到的签名
	 * @param  array  $param 	 签名数组
	 * @param  string $code      安全校验码
	 * @param  string $sign_type 签名类型
	 * @return boolean true正确，false失败
	 */
	public static function checkSign($sign, $param, $code, $sign_type = 'MD5'){
		return $sign == self::getSign($param, $code, $sign_type);
	}

	/**
	 * 创建签名字符串
	 * 
	 * @param  string $param 需要加密的字符串
	 * @param  string $type  签名类型 默认值：MD5
	 * @return string 签名结果
	 */
	private static function createSign($param, $type = 'MD5'){
		$type = strtolower($type);
		if($type == 'md5'){
			$str = md5($param);
			$str = sha1($str);
			$str = md5($str);
			$str = sha1($str);
			return substr($str, 4, 32);
		}
		if($type == 'dsa'){
			exit('DSA 签名方法待后续开发，请先使用MD5签名方式');
		}
		exit("接口暂不支持" . $type . "类型的签名方式");
	}
}