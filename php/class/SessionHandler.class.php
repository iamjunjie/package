<?php
/**
 * 自定义session处理类
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-06-24
 */
//session存入数据库
class DBSessionHandler{

	//创建存放session表的SQL语句
	private $sql = "CREATE TABLE `session` (
						`id` varchar(255) NOT NULL COMMENT 'session id',
						`data` text NOT NULL COMMENT 'session 数据',
						`expires` int(11) NOT NULL DEFAULT '0' COMMENT 'session 失效时间',
						PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	//数据库地址
	private $host = '127.0.0.1';

	//用户名
	private $user = 'root';
	
	//密码
	private $password = '123456';

	//数据库
	private $database = 'test';
	
	//数据库连接标识
	private $link = null;
	
	//session生命周期
	private $lifeTime = null;

	/**
	 * open回调函数类似于类的构造函数，在会话打开的时候会被调用
	 * 这是自动开始会话或者通过调用session_start()手动开始会话之后第一个被调用的回调函数
	 * 
	 * @param  string $savePath    保存路径
	 * @param  string $sessionName session名字
	 * @return boolean true成功，false失败
	 */
	public function open($savePath, $sessionName){
		if(($this->link=@mysql_connect($this->host, $this->user, $this->password)) && (@mysql_select_db($this->database, $this->link))){
			$this->lifeTime = ini_get('session.gc_maxlifetime');
			return true;
		}
		return false;
	}

	/**
	 * close回调函数类似于类的析构函数，在write回调函数调用之后调用
	 * 当调用session_write_close()函数之后，也会调用close回调函数
	 * 
	 * @return boolean true成功，false失败
	 */
	public function close(){
		$this->gc(ini_get('session.gc_maxlifetime'));
        return @mysql_close($this->link); 
	}

	/**
	 * 如果会话中有数据，read回调函数必须返回将会话数据编码（序列化）后的字符串，如果会话中没有数据，read回调函数返回空字符串
	 * 在自动开始会话或者通过调用session_start()函数手动开始会话之后，PHP内部调用read回调函数来获取会话数据，在调用read之前，PHP会调用open回调函数
	 * read回调返回的序列化之后的字符串格式必须与write回调函数保存数据时的格式完全一致，PHP会自动反序列化返回的字符串并填充$_SESSION超级全局变量，虽然数据看起来和serialize()函数很相似，但是需要提醒的是，它们是不同的
	 * 
	 * @param  string $sessionID session id
	 * @return mixed
	 */
	public function read($sessionID){
		$sql    = sprintf(" SELECT data FROM `session` WHERE `id`='%s' AND expires>=%s ", $sessionID, time());
		$query  = mysql_query($sql, $this->link);
		if($row = mysql_fetch_assoc($query)){
			return $row['data'];
		}
		return '';
	}

	/**
	 * 在会话保存数据时会调用write回调函数，此回调函数接收当前会话ID以及$_SESSION中数据序列化之后的字符串作为参数
	 * 序列化后的数据将和会话ID关联在一起进行保存，当调用read回调函数获取数据时，所返回的数据必须要和传入write回调函数的数据完全保持一致
	 * PHP会在脚本执行完毕或调用session_write_close()函数之后调用此回调函数，注意：在调用完此回调函数之后，PHP内部会调用close回调函数
	 * 
	 * @param  string $sessionID session id
	 * @param  string $data      session 数据
	 * @return boolean true成功，false失败
	 */
	public function write($sessionID, $data){
		$dbData  = array(
			'id'      => $sessionID,
			'data'    => $data,
			'expires' => time() + $this->lifeTime,
		);
		foreach ($dbData as $k => $v) {
			$fields[] = "`{$k}`";
			$values[] = "'{$v}'";
		}
		$sql = sprintf(" REPLACE INTO `session`(%s) VALUES(%s) ", implode(',', $fields), implode(',', $values));
		mysql_query($sql, $this->link);
		return mysql_affected_rows($this->link);
	}

	/**
	 * 当调用session_destroy()函数，或者调用session_regenerate_id()函数并且设置destroy参数为TRUE时，会调用此回调函数
	 * 
	 * @param  string $sessionID session id
	 * @return boolean true成功，false失败
	 */
	public function destroy($sessionID){
		$sql = sprintf(" DELETE `session` WHERE `id`='%s' ", $sessionID);
		mysql_query($sql, $this->link);
		return mysql_affected_rows($this->link);
	}

	/**
	 * 为了清理会话中的旧数据，PHP会不时的调用垃圾收集回调函数
	 * 
	 * @param  int $lifeTime 生命周期
	 * @return boolean true成功，false失败
	 */
	public function gc($lifeTime){
		$sql = sprintf(" DELETE `session` WHERE `expires` < '%s' ", time());
		mysql_query($sql, $this->link);
		return mysql_affected_rows($this->link);
	}
}

$session = new DBSessionHandler();
session_set_save_handler(
	array(&$session, 'open'),
	array(&$session, 'close'),
	array(&$session, 'read'),
	array(&$session, 'write'),
	array(&$session, 'destroy'),
	array(&$session, 'gc')
);

session_id('parentinfo');
//测试
session_start();
$_SESSION['parent'] = array('parent_id' => '123', 'name' => '小杰子');
printf('<meta charset="utf-8" /><pre>%s<pre>', print_r($_SESSION['parent'], true));