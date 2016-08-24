<?php
/**
 * 数据库类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-06-04
 */
class DB {

	//数据库连接标识
	private $link = null;

	//数据库实例
	private static $instance = null;

	/**
	 * 获取实例
	 * 
	 * @return object
	 */
	public static function getInstance(){
		if(is_null(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 连接数据库
	 * 
	 * @param  string $server 数据库服务器地址
	 * @param  string $user   用户名
	 * @param  string $pwd    密码
	 */
	public function connect($server, $user, $pwd){
		$this->link = mysql_connect($server, $user, $pwd);
		if(!$this->link){
			exit('Could not connect: ' . mysql_error());
		}
		mysql_set_charset("UTF8", $this->link);
	}

	/**
	 * 查询
	 * 
	 * @param  stirng $sql 执行的SQL语句
	 * @return array
	 */
	public function query($sql){
		$query = mysql_query($sql, $this->link);
		if(!$query){ exit($sql); }
		$result = array();
		while($result[] = mysql_fetch_assoc($query));
		array_pop($result);
		return $result;
	}

	//构造函数私有化，确保单例
	private function __construct(){ }

	//克隆函数私有化，确保单例
	private function __clone(){ }
}