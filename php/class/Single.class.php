<?php
/**
 * PHP设计模式：单例模式
 *
 * 保证一个类仅有一个实例，并且提供一个访问它的全局访问点
 * 三个特点：
 * 1、一个类只有一个实例
 * 2、它必须自行创建这个实例
 * 3、必须自行向整个系统提供这个实例
 *
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-04-16
 */
class Single {

	//静态成员变量，保存全局实例
	private static $instance = null;

	//构造方法私有化，保证外界无法直接实例化
	private function __construct(){ }

	//防止用户克隆实例
	private function __clone(){ }

	/**
	 * 获取此类的唯一实例
	 * 
	 * @return object
	 */
	public static function &getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 测试
	 */
	public function test(){
		echo 'Single Test!';
	}
}

//测试
$instance = &Single::getInstance();
$instance->test();