<?php
/**
 * 树
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-06-16
 */
class Tree {

	//原始数据
	private static $data = array();

	//父级字段名
	private static $pid = null;

	//主键字段名
	private static $id = null;

	//文本字段名
	private static $name = null;

	//填充字符
	private static $char = '—';

	//树的风格
	private static $style = 2;

	//结果数据
	private static $tree = array();

	/**
	 * 获取树
	 * 
	 * @param  array $params 参数
	 * 	                     data  原始数据
	 * 	                     pid   父级字段名
	 * 	                     id    主键字段名
	 * 	                     name  文本字段名
	 * 	                     char  填充字符
	 * 	                     style 风格（1:适用于select框，2:数组）
	 * @return array
	 */
	public static function getTree($params){
		self::$tree  = array();
		self::$data  = $params['data'];
		self::$pid   = $params['pid'];
		self::$id    = $params['id'];
		self::$name  = $params['name'];
		self::$char  = isset($params['char']) ? $params['char'] : self::$char;
		self::$style = isset($params['style']) ? $params['style'] : self::$style;
		$root = self::getRoot();
		if(empty($root)){ return self::$data; }
		foreach ($root as $key => $value) {
			$level          = 1;
			$value['depth'] = 0;
			switch (self::$style) {
				case 1:
					self::$tree[] = $value;
					self::buildLeaf($value, $level);
					break;
				case 2:
					$value['sublist'] = array();
					self::buildLeaf($value, $level);
					self::$tree[] = $value;
					break;
				default:
					self::$tree[] = $value;
					self::buildLeaf($value, $level);
					break;
			}
		}
		return self::$tree;
	}

	/**
	 * 获取根
	 * 
	 * @return array
	 */
	private static function getRoot(){
		$root = array();
		foreach (self::$data as $key => $value) {
			if(empty($value[self::$pid])){
				unset(self::$data[$key]);
				$root[] = $value;
			}
		}
		return $root;
	}

	/**
	 * 构建叶子
	 * 
	 * @param  array $item  数据项
	 * @param  int   $level 层级数
	 */
	private static function buildLeaf(&$item, $level){
		if(!empty(self::$data)){
			foreach (self::$data as $key => $value) {
				$value['depth'] = $level;
				if($value[self::$pid] != $item[self::$id]){ continue; }
				unset(self::$data[$key]);
				switch (self::$style) {
					case 1:
						$value[self::$name] = str_repeat(self::$char, $level*2) . $value[self::$name];
						self::$tree[] = $value;
						self::buildLeaf($value, $level + 1);
						break;
					case 2:
						$value['sublist'] = array();
						self::buildLeaf($value, $level + 1);
						array_push($item['sublist'], $value);
						break;
					default:
						$value[self::$name] = str_repeat(self::$char, $level*2) . $value[self::$name];
						self::$tree[] = $value;
						self::buildLeaf($value, $level + 1);
						break;
				}
			}
		}
	}
}

$data = array(
	array('parent_id' => 0, 'id' => 1, 'name' => '北京', 'address' => ''),
	array('parent_id' => 1, 'id' => 2, 'name' => '海淀', 'address' => ''),
	array('parent_id' => 1, 'id' => 3, 'name' => '朝阳', 'address' => ''),
	array('parent_id' => 0, 'id' => 4, 'name' => '河北', 'address' => ''),
	array('parent_id' => 4, 'id' => 5, 'name' => '邯郸', 'address' => ''),
	array('parent_id' => 4, 'id' => 6, 'name' => '唐山', 'address' => ''),
	array('parent_id' => 2, 'id' => 7, 'name' => '海淀1', 'address' => ''),
	array('parent_id' => 2, 'id' => 8, 'name' => '海淀2', 'address' => ''),
	array('parent_id' => 3, 'id' => 9, 'name' => '朝阳1', 'address' => ''),
	array('parent_id' => 3, 'id' => 10, 'name' => '朝阳2', 'address' => ''),
);

$one_style = Tree::getTree(array(
	'data' => $data,
	'pid'  => 'parent_id',
	'id'   => 'id',
	'name' => 'name',
	'char' => '—',
	'style' => 1,
));

$two_style = Tree::getTree(array(
	'data' => $data,
	'pid'  => 'parent_id',
	'id'   => 'id',
	'name' => 'name',
	'style' => 2,
));

printf('<meta charset="utf-8" /> <pre>风格一：%s</pre> <pre>风格二：%s<pre> <a href="%s" target="_black">查看源代码</a>', 
	print_r($one_style, true), print_r($two_style, true), 
	"//{$_SERVER['HTTP_HOST']}/code.php?vsc=" . urlencode(__FILE__)
);