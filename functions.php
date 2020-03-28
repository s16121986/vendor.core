<?php
use Stdlib\DateTime;

function lang() {
	return call_user_func_array(['Translation', 'translate'], func_get_args());
}

function langesc($string) {
	return str_replace(['\'', "\n"], ['&#39;', ' '], lang($string));
}

function transliterate($s) {
	$s = (string) $s; // преобразуем в строковое значение
	$s = strip_tags($s); // убираем HTML-теги
	$s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
	$s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
	$s = trim($s); // убираем пробелы в начале и конце строки
	$s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
	$s = strtr($s, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => ''));
	$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
	$s = str_replace(" ", "_", $s); // заменяем пробелы знаком минус
	return $s; // возвращаем результат
}

function format($value, $format) {
	return Format::exec($value, $format);
}

function getWordDeclension($number, $variants, $addNumber = false) {
	$number = (int) abs($number);
	switch (true) {
		case ($number % 100 == 1 || ($number % 100 > 20) && ($number % 10 == 1)):$i = 0;break;
		case ($number % 100 == 2 || ($number % 100 > 20) && ($number % 10 == 2)):
		case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 3)):
		case ($number % 100 == 4 || ($number % 100 > 20) && ($number % 10 == 4)):
			$i = 1;
			break;
		default:$i = 2;
	}
	if (is_string($variants)) {
		$variants = explode(',', $variants);
	}
	return ($addNumber ? $number . ' ' : '') 
		. (isset($variants[$i]) ? $variants[$i] : null);
}

function getNumberDeclension($number, $variants) {
	$number = (int) abs($number);
	switch (true) {
		case ($number % 100 == 1 || ($number % 100 > 20) && ($number % 10 == 1)):$i = 0;break;
		case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 3)):$i = 2;break;
		case ($number % 100 == 2 || ($number % 100 > 20) && ($number % 10 == 2)):
		case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 6)):
		case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 7)):
		case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 8)):
			$i = 1;
			break;
		default:$i = 3;
	}
	if (is_string($variants)) {
		$variants = explode(',', $variants);
	}
	return (isset($variants[$i]) ? $variants[$i] : null);
}

function NumberInWords($number, $params, $format = null) {
	
	$elements = array(
		'L' => null,//Код локализации
		'SN' => true,//Включать/не включать название предмета исчисления
		'FN' => true,//Включать/не включать название десятичных частей предмета исчисления
		'FS' => false//Дробную часть выводить прописью/числом
	);	
	if (is_string($format)) {
		$formatTemp = $format;
		$format = array();
		$ei = array_keys($elements);
		$parts = explode(';', $formatTemp);
		if ('' === $parts[count($parts) - 1]) {
			array_pop($parts);
		}
		foreach ($parts as $i => $part) {
			$pp = explode('=', $part);
			if (isset($pp[1])) {
				if (isset($elements[$pp[0]])) {
					$format[$pp[0]] = $pp[1];
				}
			} else {
				if (isset($ei[$i])) {
					$format[$ei[$i]] = $pp[0];
				}
			}
		}
	} elseif (!is_array($format)) {
		$format = array();
	}
	$format = array_merge($elements, $format);
	
	
	//return Format::formatNumber($number, $format);
	$str = (string)$number;
	
}

function clearUrl($url) {
	$url = (string) $url; // преобразуем в строковое значение
	$url = strip_tags($url); // убираем HTML-теги
	$url = str_replace(array("\n", "\r"), " ", $url); // убираем перевод каретки
	$url = preg_replace("/\s+/", ' ', $url); // удаляем повторяющие пробелы
	$url = trim($url); // убираем пробелы в начале и конце строки
	$url = function_exists('mb_strtolower') ? mb_strtolower($url, 'utf8') : strtolower($url); // переводим строку в нижний регистр (иногда надо задать локаль)
	$url = strtr($url, array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => ''));
	$url = preg_replace("/[^0-9a-z-_ ]/i", "", $url); // очищаем строку от недопустимых символов
	$url = str_replace(" ", "_", $url); // заменяем пробелы знаком минус
	return $url;
}

function __initDateTimeArgument($date) {
	return DateTime::factory($date);
}

function now() {
	return DateTime::now();
}

function CurrentDate() {
	return __initDateTimeArgument(null);
}
function Year($date = null) {
	return __initDateTimeArgument($date)->getYear();
}
function Month($date = null) {
	return __initDateTimeArgument($date)->getMonth();
}
function Day($date = null) {
	return __initDateTimeArgument($date)->getDay();
}
function Hour($date = null) {
	return __initDateTimeArgument($date)->getHour();
}
function Minute($date = null) {
	return __initDateTimeArgument($date)->getMinute();
}
function Second($date = null) {
	return __initDateTimeArgument($date)->getSecond();
}
function DayOfYear($date = null) {
	return (int)__initDateTimeArgument($date)->format('z');
}
function WeekOfYear($date = null) {
	return (int)__initDateTimeArgument($date)->format('W');
}
function BegOfYear($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime
		->setDate($datetime->getYear(), 1, 1)
		->setTime(0, 0, 0);
	return $datetime;
}
function EndOfYear($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime
		->setDate($datetime->getYear(), 12, 31)
		->setTime(23, 59, 59);
	return $datetime;
}
function BegOfQuarter($date = null) {
	
}
function EndOfQuarter($date = null) {
	
}
function BegOfMonth($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime->modify('first day of this month');
	$datetime->setTime(0, 0, 0);
	return $datetime;
}
function EndOfMonth($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime->modify('last day of this month');
	$datetime->setTime(23, 59, 59);
	return $datetime;
}
function AddMonth($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime->modify('+1 month');
	return $datetime;
}
function WeekDay($date = null) {
	return __initDateTimeArgument($date)->getWeekDay();
}
function BegOfWeek($date = null) {
	$datetime = __initDateTimeArgument($date);
	$d = $datetime->getWeekDay();
	if ($d > 1) {
		$datetime->modify('-' . ($d - 1) . ' day');
	}
	$datetime->setTime(0, 0, 0);
	return $datetime;
}
function EndOfWeek($date = null) {
	$datetime = __initDateTimeArgument($date);
	$d = $datetime->getWeekDay();
	if ($d < 7) {
		$datetime->modify('+' . (7 - $d) . ' day');
	}
	$datetime->setTime(23, 59, 59);
	return $datetime;
}
function BegOfHour($date = null) {
	
}
function EndOfHour($date = null) {
	
}
function BegOfMinute($date = null) {
	
}
function EndOfMinute($date = null) {
	
}
function BegOfDay($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime->setTime(0, 0, 0);
	return $datetime;
}
function EndOfDay($date = null) {
	$datetime = __initDateTimeArgument($date);
	$datetime->setTime(23, 59, 59);
	return $datetime;
}

function call_enum_func($enum, $func, $arg) {
	return call_user_func('\\' . $enum . '::' . $func, $arg);
}

function format_data_int() {
	$args = func_get_args();
	$array = array_shift($args);
	foreach ($args as $n) {
		$array[$n] = (int)$array[$n];
	}
	return $array;
}