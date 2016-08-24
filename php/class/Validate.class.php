<?php
/**
 * 验证类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2016-03-21
 */
class Validate {

	//被验证数据
	private static $data  = array();

	//被验证的字段
	private static $field = '';

	//验证规则
	private static $rule  = array();

	//正则表达式
	private static $patterns = array(
		'email'   => "/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", //邮箱
		'url'     => "/((^http)|(^https)|(^ftp)):\/\/([A-Za-z0-9]+\.[A-Za-z0-9]+)+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/", //URL
		'english' => "/^[a-zA-Z]*$/", //英文
		'chinese' => "/^[\x{4e00}-\x{9fa5}]+$/u", //中文
		'tel'     => "/(^[0-9]{3,4}\-[0-9]{7,8}$)|(^[0-9]{7,8}$)|(^\([0-9]{3,4}\)[0-9]{3,8}$)|(^0{0,1}13[0-9]{9}$)|(13\d{9}$)|(15[0135-9]\d{8}$)|(18[267]\d{8}$)/", //固话
		'mobile'  => "/^1[3|4|5|7|8][0-9]{9}$/", //手机
		'id_card' => "/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", //身份证号
		'money'   => "/^(0|[1-9]\d*)(\.\d{1,2})?$/", //金额
		'pos_int' => "/^[1-9][0-9]*$/", //正整数
		'year'	  => "/^(?!0000)[0-9]{4}$/", //年
		'date'	  => "/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/", //日期
	); 

	/**
	 * 验证结果
	 * 
	 * @param  array $data  数据
	 * @param  array $rules 验证规则
	 * @return array
	 */
	public static function result($data, $rules){
		self::$data = $data;
		foreach ($rules as $field => $rule) {
			self::$field = $field;
			foreach ($rule as $key => $val) {
				self::$rule = $val;
				$method = 'vd' . ucfirst($val['method']);
				if(!self::$method()){
					return array('status' => false, 'msg' => self::$rule['msg']);
				}
			}
		}
		return array('status' => true, 'msg' => '');
	}

	/**
	 * 获取字段值
	 * 
	 * @return mixed
	 */
	private static function getVal(){
		$val = '';
		if(array_key_exists(self::$field, self::$data)){
			$val = self::$data[self::$field];
		}
		return $val;
	}

	/**
	 * 设置验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdIsset(){
		return array_key_exists(self::$field, self::$data);
	}

	/**
	 * 空验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdEmpty(){
		$val = self::getVal();
		return (boolean)strlen($val);
	}

	/**
	 * 长度验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdLength(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		$len = strlen($val);
		$res = true;
		if(isset(self::$rule['min'])){
			$res = $res && ($len>=self::$rule['min']);
		}
		if(isset(self::$rule['max'])){
			$res = $res && ($len<=self::$rule['max']);
		}
		if(isset(self::$rule['equ'])){
			$res = $res && ($len==self::$rule['equ']);
		}
		return $res;
	}

	/**
	 * 邮箱验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdEmail(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['email'], $val);
	}

	/**
	 * URL验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdUrl(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['url'], $val);
	}

	/**
	 * 英文验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdEnglish(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['english'], $val);
	}

	/**
	 * 中文验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdChinese(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['chinese'], $val);
	}

	/**
	 * 固话验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdTel(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['tel'], $val);
	}

	/**
	 * 手机验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdMobile(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['mobile'], $val);
	}

	/**
	 * 电话验证(固话/手机)
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdPhone(){
		return (self::vdTel() || self::vdMobile());
	}

	/**
	 * 身份证号验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdIdCard(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['id_card'], $val);
	}

	/**
	 * 金额验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdMoney(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['money'], $val);
	}

	/**
	 * 正整数验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdPosInt(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['pos_int'], $val);
	}

	/**
	 * 年份验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdYear(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['year'], $val);
	}

	/**
	 * 日期验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdDate(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$patterns['date'], $val);
	}

	/**
	 * 自定义正则验证
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdRegExp(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return preg_match(self::$rule['pattern'], $val);
	}

	/**
	 * 验证字段值是否在一个数组中
	 * 
	 * @return boolean true通过，false未通过
	 */
	private static function vdInArray(){
		$val = self::getVal();
		if(!strlen($val)){ return true; }
		return in_array($val, self::$rule['arr']);
	}
}
