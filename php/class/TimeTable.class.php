<?php
/**
 * 时间表
 * 
 * @author wangjunjie <1252547929@qq.com>
 * @date 2015-09-24
 */
class TimeTable {

	/**
	 * 获取时间表
	 * 
	 * @return array
	 */
	public function getTimeTable(){
		$dates = array();
		$date  = $this->getDate();
		$time  = strtotime($date);
		$week  = date('w', $time);
		//周日特殊处理
		if($week == 0){
			$week = 7;
		}
		//当前周几前几天
		$num = $week - 1;
		for($i=$num; $i>0; $i--){
			$dates[] = array(
				'week' => $week - $i,
				'date' => date('Y-m-d', strtotime("-{$i} day", $time)),
			);
		}
		//当前周几
		$dates[]  = array('week' => $week, 'date' => $date);
		//当前周几后几天
		$num = 7 - $week;
		for($i=1; $i<=$num; $i++){
			$dates[] = array(
				'week' => $week + $i,
				'date' => date('Y-m-d', strtotime("+{$i} day", $time)),
			);
		}
		return $dates;
	}

	/**
	 * 获取日期
	 * 
	 * @return string
	 */
	private function getDate(){
		$year  = $this->get('year', date('Y'));
		$month = $this->get('month', date('m'));
		if($month<0 || $month>12){
			$month = date('m');
		}
		$day = $this->get('day', date('d'));
		//给定月份中应有的天数(28-31)
		$num = date('t', strtotime("{$year}-{$month}"));
		if($day<0 || $day>$num){
			$day = $num;
		}
		return "{$year}-{$month}-{$day}";
	}

	/**
	 * 获取URL参数
	 * 
	 * @param  string $name    参数名
	 * @param  mixed  $default 默认返回值
	 * @return mixed
	 */
	private function get($name, $default = ''){
		$data = $default;
		if(array_key_exists($name, $_GET)){
			$data = $_GET[$name];
		}
		return $data;
	}
}

echo '<meta charset="utf-8" />';
$weeks = array(1=>'周一',2=>'周二',3=>'周三',4=>'周四',5=>'周五',6=>'周六',7=>'周日');
$timeTable = new TimeTable();
$data = $timeTable->getTimeTable();
$time = strtotime("-7 day", strtotime($data[0]['date']));
printf('<a href="?year=%s&month=%s&day=%s">Prev</a><br />', date('Y', $time), date('m', $time), date('d', $time));
foreach ($data as $key => $value) {
	echo $value['date'] . '|' . $value['week'] . '|' . $weeks[$value['week']] . '<br />';
}
$time = strtotime("+7 day", strtotime($data[6]['date']));
printf('<a href="?year=%s&month=%s&day=%s">Next</a><br />', date('Y', $time), date('m', $time), date('d', $time));