<?php
namespace Translation;

abstract class Calendar{
	
	private static $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	private static $monthsShort = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	private static $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
	private static $weekDaysShort = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'St', 'Su'];
	private static $cache = [];

	public static function getMonth($index) {
		$months = self::getMonths();
		return $months[$index];
	}

	public static function getMonths() {
		return self::_get('months', self::$months);
	}

	public static function getMonthShort($index) {
		$months = self::getMonthsShort();
		return $months[$index];
	}

	public static function getMonthsShort() {
		return self::_get('months_short', self::$monthsShort);
	}

	public static function getWeekDay($day) {
		$days = self::getWeekDays();
		return $days[$day];
	}

	public static function getWeekDays() {
		return self::_get('weekdays', self::$weekDays);
	}

	public static function getWeekDayShort($index) {
		$days = self::getWeekDaysShort();
		return $days[$index];
	}

	public static function getWeekDaysShort() {
		return self::_get('weekdays_short', self::$weekDaysShort);
	}
	
	private static function _get($cache, $array, $path = '') {
		if (!isset(self::$cache[$cache])) {
			self::$cache[$cache] = [];
			foreach ($array as $i => $n) {
				self::$cache[$cache][$i + 1] = lang($path . $n);
			}
		}
		return self::$cache[$cache];
	}
	
}