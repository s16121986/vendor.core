<?php

namespace Stdlib;

use DateTime as BaseDateTime;
use DateTimeZone as BaseDateTimeZone;
use Stdlib\DateTime\Format;
use Exception;

class DateTime extends BaseDateTime {

	private static $formats = [];

	public static function init($serverTimezone = null, $clientTimezone = null) {
		DateTimezone::setServer($serverTimezone ?: date_default_timezone_get());
		if ($clientTimezone)
			DateTimezone::setClient($clientTimezone);
		Format::init();
	}

	public static function factory($date, $timezone = null) {
		$factoryDate = new self('now', DateTimezone::getServer());

		if ($date instanceof self) {
			$factoryDate->setTimezone($date->getTimezone());
			$factoryDate->setTimestamp($date->getTimestamp());
		} elseif (is_int($date)) {
			$factoryDate->setTimestamp($date);
		} elseif (is_string($date)) {
			//$dt = new BaseDateTime($date, $timezone);
			//$dt = self::createFromFormat('Y-m-d', $date);
			$factoryDate->setTimestamp(strtotime($date));
		}

		$factoryDate->setTimezone($timezone);

		return $factoryDate;
	}

	public static function now() {
		return self::factory(null);
	}

	public static function setFormat($alias, $format) {
		//$regexp = '/' . str_replace(array('.', '?', '|'), array('\\.', '\\?', '\\|'), $alias) . '/';
		//self::$formats[$alias] = [$regexp, $format];
		self::$formats[] = [$alias, $format, '~' . count(self::$formats) . '~'];
	}

	public static function getFormat($format) {
		if (isset(self::$formats[$format]))
			return self::$formats[$format][1];
		return $format;
	}

	public static function serverDate($datetime = null) {
		return self::factory($datetime, DateTimezone::getServer())->format('server.date');
	}

	public static function serverTime($datetime = null) {
		return self::factory($datetime, DateTimezone::getServer())->format('server.time');
	}

	public static function serverDatetime($datetime = null) {
		return self::factory($datetime, DateTimezone::getServer())->format('server.datetime');
	}

	public function __construct($time = 'now', BaseDateTimeZone $timezone = null) {
		parent::__construct($time);
		$this->setTimezone($timezone);
	}

	public function format($format) {
		foreach (self::$formats as $f) {
			if (is_callable($f[1]))
				$format = str_replace($f[0], $f[2], $format);
			else
				$format = str_replace($f[0], $f[1], $format);
		}

		$format = parent::format($format);

		$datetime = $this;
		$formats = self::$formats;
		$format = preg_replace_callback('/~(\d+)~/', function ($matches) use ($datetime, $formats) {
			return call_user_func($formats[$matches[1]][1], $datetime);
		}, $format);

		return $format;
	}

	public function setTimezone($timezone) {
		if (null === $timezone)
			$timezone = DateTimeZone::getClient();
		if (is_string($timezone) && DateTimeZone::get($timezone))
			$timezone = DateTimeZone::get($timezone);

		if (!$timezone)
			return $this;

		return parent::setTimezone($timezone);
	}

	public function formatTime() {
		return parent::format('time');
	}

	public function formatDate() {
		return parent::format('date');
	}

	public function formatDatetime() {
		return parent::format('datetime');
	}

	public function setYear($year) {
		$this->setDate($year, $this->getMonth(), $this->getDay());
		return $this;
	}

	public function setMonth($month) {
		$this->setDate($this->getYear(), $month, $this->getDay());
		return $this;
	}

	public function setDay($day) {
		$this->setDate($this->getYear(), $this->getMonth(), $day);
		return $this;
	}

	public function setHours($hours) {
		$this->setTime($hours, $this->getMinute());
		return $this;
	}

	public function setMinutes($minutes) {
		$this->setTime($this->getHour(), $minutes);
		return $this;
	}

	public function setSeconds($seconds) {
		$this->setTime($this->getHour(), $this->getMinute(), $seconds);
		return $this;
	}

	public function getYear() {
		return (int) $this->format('Y');
	}

	public function getMonth() {
		return (int) $this->format('n');
	}

	public function getDay() {
		return (int) $this->format('j');
	}

	public function getWeekDay() {
		return (int) $this->format('N');
	}

	public function getHour() {
		return (int) $this->format('H');
	}

	public function getMinute() {
		return (int) $this->format('i');
	}

	public function getSecond() {
		return (int) $this->format('s');
	}

}
