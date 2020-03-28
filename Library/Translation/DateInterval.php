<?php
namespace Translation;

abstract class DateInterval{
	
	public static function translate($dateInterval) {
		$parts = [
			'y' => 'год,года,лет',
			'm' => 'месяц,месяца,месяцев',
			'd' => 'день,дня,дней',
			'h' => 'час,часа,часов',
			'i' => 'минуту,минуты,минут',
			's' => 'секунду,секунды,секунд'
		];
		$namesWeek = 'неделю,недели,недель';
		$name = [];
		foreach ($parts as $k => $variants) {
			if ($dateInterval->$k) {
				$iv = $dateInterval->$k;
				if ($k == 'd' && $iv >= 7) {
					$iv = floor($iv / 7);
					$variants = $namesWeek;
				}
				$name[] = ($iv > 1 ? $iv . ' ' : '') . getWordDeclension($iv, $variants);
				break;
			}
		}
		if (empty($name))
			$name[] = getWordDeclension(1, $parts['s']);
		if ($dateInterval->invert)
			$name[] = 'назад'; 
		return implode(' ', $name);
	}
	
}