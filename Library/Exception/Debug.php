<?php

class Debug{

	private static $memoryUsage;

	public static function start() {
		self::$memoryUsage = [];
		self::point('start');
	}

	public static function point($name = null) {
		self::$memoryUsage[$name ?? count(self::$memoryUsage)] = memory_get_usage();
	}

	public static function render() {
		$start = 0;
		foreach (self::$memoryUsage as $k => $v) {
			echo $k . '. ' . ($v - $start) . "\n";
			$start = $v;
		}
	}

}