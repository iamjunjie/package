<?php
/**
 * 魔术方法
 *
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-04-19
 */
class MagicMethod {
	/**
	 * 构造函数
	 */
	public function __construct(){ }

	/**
	 * 析构函数
	 */
	public function __destruct(){ }

	/**
	 * 属性赋值
	 * 
	 * @param string $name  属性名
	 * @param mixed  $value 属性值
	 */
	public function __set($name, $value){ }

	/**
	 * 读取属性值
	 * 
	 * @param  string $name  属性名
	 * @return mixed  属性值
	 */
	public function __get($name){ }

	/**
	 * 方法未定义或不可见时，此方法被调用
	 * 
	 * @param  string $name 被调用的方法名
	 * @param  array  $args 被调用的方法参数
	 * @return mixed
	 */
	public function __call($name, $args){ }

	/**
	 * 获取对象的字符串信息
	 * 
	 * @return string
	 */
	public function __toString(){ }
}