<?php

namespace Translation;

abstract class Util {

	private static $encoding = 'utf-8';
	private static $languages = [
		'йцукенгшщзхъфывапролджэячсмитьбюЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ',
		'qwertyuiop[]asdfghjkl;\'zxcvbnm,.QWERTYUIOP{}ASDFGHJKL:"ZXCVBNM<>'
	];

	public static function transliterate($string, $space = ' ') {
		$string = (string) $string; // преобразуем в строковое значение
		$string = strip_tags($string); // убираем HTML-теги
		$string = str_replace(["\n", "\r"], '', $string); // убираем перевод каретки
		$string = preg_replace('/\s+/', ' ', $string); // удаляем повторяющие пробелы
		$string = trim($string); // убираем пробелы в начале и конце строки
		$string = function_exists('mb_strtolower') ? mb_strtolower($string) : strtolower($string); // переводим строку в нижний регистр (иногда надо задать локаль)
		$string = strtr($string, ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ы' => 'y', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'ъ' => '', 'ь' => '']);
		$string = preg_replace('/[^0-9a-z-_ ]/i', '', $string); // очищаем строку от недопустимых символов
		$string = str_replace(' ', $space, $string);
		return $string;
	}

	public static function getSpellVariants($term) {
		$l = mb_strlen($term, self::$encoding);
		$str = '';
		for ($i = 0; $i < $l; $i++) {
			$chr = mb_substr($term, $i, 1, self::$encoding);
			if ($chr != ' ') {
				$pos = false;
				foreach (self::$languages as $lk => $lang) {
					$pos = mb_strpos($lang, $chr, 0, self::$encoding);
					if (false !== $pos) {
						$toLang = self::$languages[1 - $lk];
						$chr = mb_substr($toLang, $pos, 1, self::$encoding);
						break;
					}
				}
			}
			$str .= $chr;
		}
		if ($term === $str) {
			return array($term);
		}
		return array($term, $str);
	}

	public static function getWordDeclension($number, $variants) {
		$number = (int) abs($number);
		switch (true) {
			case ($number % 100 == 1 || ($number % 100 > 20) && ($number % 10 == 1)):$i = 0;
				break;
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
		return (isset($variants[$i]) ? $variants[$i] : null);
	}

	public static function getNumberDeclension($number, $variants) {
		$number = (int) abs($number);
		switch (true) {
			case ($number % 100 == 1 || ($number % 100 > 20) && ($number % 10 == 1)):$i = 0;
				break;
			case ($number % 100 == 3 || ($number % 100 > 20) && ($number % 10 == 3)):$i = 2;
				break;
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

}
