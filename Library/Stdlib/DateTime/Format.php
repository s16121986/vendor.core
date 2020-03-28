<?php
namespace Stdlib\DateTime;

use Stdlib\DateTime;
use Translation\DateInterval as TranslationDateInterval;
use Translation\Calendar;

abstract class Format{
	
	public static function init() {
		
		DateTime::setFormat('date?time', function (DateTime $datetime) {
			if (DateTime::serverDate() === DateTime::serverDate($datetime))
				return DateTime::getFormat('time');
			return DateTime::getFormat('datetime');
		});
		DateTime::setFormat('date|time', function (DateTime $datetime) {
			if (DateTime::serverDate() === DateTime::serverDate($datetime))
				return DateTime::getFormat('time');
			return DateTime::getFormat('date');
		});
		DateTime::setFormat('time.diff', function (DateTime $datetime) { return Format::interval($datetime); });
		
		foreach ([
			'server.datetime' => 'Y-m-d H:i:s',
			'server.date' => 'Y-m-d',
			'server.time' => 'H:i:s',
			'datetime' => 'd.m.Y H:i',
			'date' => 'd.m.Y',
			'time' => 'H:i'
		] as $alias => $format) {
			DateTime::setFormat($alias, $format);
		}
		
		DateTime::setFormat('F', function (DateTime $datetime) { return Calendar::getMonth($datetime->getMonth()); });
		DateTime::setFormat('M', function (DateTime $datetime) { return Calendar::getMonthsShort($datetime->getMonth()); });
		DateTime::setFormat('D', function (DateTime $datetime) { return Calendar::getWeekDay($datetime->getWeekDay()); });
		DateTime::setFormat('l', function (DateTime $datetime) { return Calendar::getWeekDayShort($datetime->getWeekDay()); });
	}
	
	public static function interval(DateTime $datetime) {
		return TranslationDateInterval::translate(now()->diff($datetime));
	}
	
}