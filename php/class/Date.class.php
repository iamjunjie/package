<?php
class Date {

	/**
	 * 获取一周中每天的日期
	 * 
	 * @param  array $params 参数
	 *                       type:获取的方式（1:年和周次），默认1
	 *                       year:年份，week:周次               
	 * @return array
	 */
	public static function getWeekDate($params){
		$week_date_arr = array();
		$type = isset($params['type']) ? $params['type'] : 1;
		switch ($type) {
			case 1: //根据年份和周次
				$year  = $params['year'];
				$week  = $params['week'];
				$time  = mktime(0, 0, 0, 1, 1, $year);
				$weekn = date('N', $time); //数字表示的星期中的第几天，1（表示星期一）到 7（表示星期天）
				$weekw = date('W', $time); //年份中的第几周，每周从星期一开始，42（当年的第 42 周）
				$day   = ($weekw==1 ? (2-$weekn) : (9-$weekn)) + 7*($week - 1);
				for($i=0; $i<7; $i++){
					$week_date_arr[$i + 1] = date('Y-m-d', mktime(0, 0, 0, 1, $day + $i, $year));
				}
				break;
		}
		return $week_date_arr;
	}
}

$test = Date::getWeekDate(array(
	'year' => 2015,
	'week' => 1,
));
var_dump($test);