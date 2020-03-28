<?php
namespace Exception;

use Format;

abstract class Timeline{
	
	private static $format = 'мс,с,мин';
	private static $points = array();
	
	public static function point($label) {
		$point = new \stdClass();
		$point->label = $label;
		$point->time = microtime(true);
		self::$points[] = $point;
	}
	
	public static function start($lebel) {
		self::$points = array();
		self::point($lebel);
	}
	
	public static function log() {
		$t = null;
		foreach (self::$points as $point) {
			echo $point->label;
			if ($t) {
				echo ': ', self::format($point->time - $t);
			}
			echo '<br />';
			$t = $point->time;
		}
		echo 'total: ', self::format($t - self::$points[0]->time);
	}
	
	private static function format($t) {
		return $t * 1000;
		return Format::formatNumberUnits($t * 1000000, self::$format);
	}
	
}