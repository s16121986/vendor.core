<?php
namespace Stdlib;

use DateTimeZone as Base;

class DateTimezone extends Base{
	
	private static $timezones = array();

	public static function setServer($timezone, $setSystemGlobal = true) {
		if ($setSystemGlobal) {
			date_default_timezone_set($timezone);
		}
		self::set('server', $timezone);
	}

	public static function getServer() {
		return self::$timezones['server'];
	}

	public static function setClient($timezone) {
		self::set('client', $timezone);
	}

	public static function getClient() {
		return self::get('client');
	}
	
	public static function set($name, $timezone) {
		self::$timezones[$name] = new self($timezone);
	}
	
	public static function get($timezone) {
		return isset(self::$timezones[$timezone]) ? self::$timezones[$timezone] : self::$timezones['server'];
	}
}