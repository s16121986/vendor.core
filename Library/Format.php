<?php

use Stdlib\DateTime;

abstract class Format {

	const S = ';';

	/*const SUC = 'SUC';
	const SLC = 'SLC';
	const SST = 'SST';
	const SUC = 'SUC';
	const SUC = 'SUC';*/

	const TL = 'TL';
	const TE = '';
	const TA = '';

	//Строка, представляющая логическое значение Ложь.
	const BF = 'BF';
	//Строка, представляющая логическое значение Истина.
	const BT = 'BT';

	//Общее число отображаемых десятичных разрядов целой части. Исходное число округляется при этом в соответствии с правилами округления, заданными для конфигурации. Если указан этот параметр, то для отображения дробной части числа обязательно указание параметра ЧДЦ, иначе дробная часть отображаться не будет.
	const ND = 'ND';
	//Число десятичных разрядов в дробной части. Исходное число округляется при этом в соответствии с правилами округления, заданными для конфигурации.
	const NFD = 'NFD';
	//Символ-разделитель целой и дробной части.
	const NDS = 'NDS';
	//Символ-разделитель групп целой части числа.
	const NGS = 'NGS';
	//Строка, представляющая нулевое значение числа. Если не задано, то представление в виде пустой строки. Если задано "ЧН=", то в виде "0". Не используется для числовых полей ввода.
	const NZ = 'NZ';
	//Нужно ли выводить лидирующие нули. Значение данного параметра не задается, собственно наличие параметра определяет вывод лидирующих нулей.
	const NLZ = 'NLZ';

	//
	const FU = 'FU';
	const FFD = 'FFD';
	const FDS = 'FDS';
	const FGS = 'FGS';
	const FZ = 'FZ';

	const NUU = 'NUU';
	const NUFD = 'NUFD';
	const NUDS = 'NUDS';
	const NUGS = 'NUGS';
	const NUZ = 'NUZ';

	const PRICE_FORMAT = 'NFD=2;NDS=,;NGS= ;';
	const NUMBER_FORMAT = 'ND=7;NGS=;NLZ=1';
	const DATE_FORMAT = 'date';
	const TIME_FORMAT = 'time';
	const DATETIME_FORMAT = 'datetime';

	private static $default = [];

	protected static function parseFormat($format, $elements) {
		if (is_string($format)) {
			$formatTemp = $format;
			$format = [];
			$ei = array_keys($elements);
			$parts = explode(self::S, $formatTemp);
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
			/*foreach ($elements as $k => $v) {
				if (preg_match('/' . $k . '=(.*)' . self::S . '/U', $formatTemp, $c)) {
					$format[$k] = $c[1];
				}
			}*/
		} else if (!is_array($format)) {
			$format = [];
		}
		return array_merge($elements, $format);
	}

	protected static function callFormat($formatString, $value) {
		$format = self::getDefault($formatString);
		if (!$format)
			return $value;

		if (is_callable($format))
			return $format($value);

		return $value;
	}

	public static function setDefaults(array $formats) {
		foreach ($formats as $k => $f) {
			self::setDefault($k, $f);
		}
	}

	public static function setDefault($type, $format) {
		self::$default[$type] = $format;
	}

	public static function getDefault($format, $default = null) {
		if (is_string($format) && isset(self::$default[$format])) {
			return self::$default[$format];
		} else if (null === $format) {
			return $default;
		}
		return $format;
	}

	public static function formatString($string, $format = null) {
		return $string;
	}

	public static function formatDate($date, $format = null) {
		return DateTime::factory($date)->format(self::getDefault($format, self::DATE_FORMAT));
	}

	public static function formatTime($time, $format = self::TIME_FORMAT) {
		return DateTime::factory($date)->format(self::getDefault($format, self::TIME_FORMAT));
	}

	public static function formatDatePeriod($dateFrom, $dateTo, $format = 'j F Y') {
		$now = now();
		$dateFrom = DateTime::factory($dateFrom);
		$dateTo = DateTime::factory($dateTo);
		if ($now->format('Y') == $dateTo->format('Y')) {
			$format = trim(str_replace(['Y', 'y'], '', $format));
		}
		$toFormat = $format;
		if ($dateFrom->format('Y') == $dateTo->format('Y')) {
			$format = trim(str_replace(['Y', 'y'], '', $format));
			if ($dateFrom->format('m') == $dateTo->format('m')) {
				$format = trim(str_replace(['m', 'n'], '', $format));
			}
		}
		return DateTime::factory($dateFrom)->format($format) . ' - ' . DateTime::factory($dateTo)->format($toFormat);
	}

	public static function formatNumber($number, $format = null) {
		$format = self::parseFormat(self::getDefault($format), [
			self::ND => 8,
			self::NFD => 0,
			self::NDS => ',',
			self::NGS => ' ',
			self::NZ => '0',
			self::NLZ => false
		]);

		if (0 == $number && false !== $format[self::NZ])
			return $format[self::NZ];

		if ($number == (int)$number)
			$format[self::NFD] = 0;

		$v = number_format($number, $format[self::NFD], $format[self::NDS], $format[self::NGS]);
		if (1 == $format[self::NLZ])
			$v = str_pad($v, $format[self::ND], '0', STR_PAD_LEFT);

		return $v;
	}

	public static function formatPrice($price, $format = null) {
		$format = self::parseFormat(self::getDefault($format, self::PRICE_FORMAT), [
			self::ND => 8,
			self::NFD => 2,
			self::NDS => ',',
			self::NGS => ' ',
			self::NZ => '0',
			self::NLZ => false
		]);
		return self::formatNumber($price, $format);
	}

	public static function formatHours($minutes, $format = 'H:i') {
		$nn = ($minutes < 0);
		if ($nn)
			$minutes = -$minutes;

		$h = floor($minutes / 60);
		$m = $minutes - ($h * 60);
		$s = 0;
		$formatTemp = $format;
		if ($format == 'label') {
			$formatTemp = '';
			if ($h)
				$formatTemp .= $h . ' ' . lang('hours&' . numberLabelPrefix($h));
			if ($m)
				$formatTemp .= ' ' . $m . ' ' . lang('minuts&' . numberLabelPrefix($m));
		} else {
			$h = str_pad($h, 2, '0', STR_PAD_LEFT);
			$m = str_pad($m, 2, '0', STR_PAD_LEFT);
			$s = str_pad($s, 2, '0', STR_PAD_LEFT);
			$formatTemp = str_replace(['H', 'i', 's'], [$h, $m, $s], $formatTemp);
		}
		return ($nn ? '-' : '') . $formatTemp;
	}

	public static function formatPhone($phone) {
		return self::callFormat('phone', $phone);
		/*if (preg_match('/^\d{10}$/', $phone))
			return '8 (' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6, 2) . '-' . substr($phone, 8, 2);

		return $phone;*/
	}

	public static function formatBoolean($value, $format) {
		$format = self::parseFormat($format, [
			self::BT => lang('True'),
			self::BF => lang('False')
		]);
		return ($value ? $format[self::BT] : $format[self::BF]);
	}

	public static function formatText($value, $format) {
		$format = self::parseFormat($format, [
			self::TL => 255,
			self::TE => '',
			self::TA => ''
		]);
	}

	public static function formatTemplate($template, $data) {
		if (is_array($data)) {
			$data = (object)$data;
		} else if (is_object($data)) {

		} else {
			$data = new stdClass();
		}
		do {
			$start = strpos($template, '{%');
			if (false === $start) {
				break;
			}
			$end = strpos($template, '%}');
			if (false === $end) {
				break;
			}
			$code = substr($template, $start + 2, $end - $start - 2);
			$v = explode(' ? ', $code);
			$cond = $v[0];
			if (!isset($v[1])) {
				break;
			}
			$v = explode(':', $v[1]);
			$true = $v[0];
			$false = (isset($v[1]) ? $v[1] : '');
			switch (true) {
				case 0 === strpos(trim($cond), '('):
					$key = substr(trim($cond), 2, -2);
					if (isset($data->$key) && $data->$key) {
						$value = trim($true);
					} else {
						$value = trim($false);
					}
					break;
				default:
					$value = '';
			}
			$template = substr($template, 0, $start) . $value . substr($template, $end + 2);
		} while (true);

		preg_match_all('/%(\w+)%/', $template, $matches);
		if ($matches) {
			foreach ($matches[1] as $key) {
				if (isset($data->$key)) {
					$value = self::formatTemplate($data->$key, $data);
				} else {
					$value = '';
				}
				$template = str_replace('%' . $key . '%', $value, $template);
			}
		}
		/*foreach ($data as $k => $v) {
			$template = str_replace('%' . $k . '%', $v, $template);
		}*/

		$template = preg_replace_callback(
			'/{var:(.*)}/U',
			function ($matches) {
				return Api\Model\Variables::get($matches[1]);
			},
			$template
		);
		return $template;
	}

	public static function formatEnum($value, $enum) {
		return '<span class="' . $enum . ' ' . $enum . '_' . call_user_func_array([$enum, 'getKey'], [$value]) . '">'
			. call_user_func_array([$enum, 'getLabel'], [$value])
			. '</span>';
	}

	public static function formatFileSize($size, $format = null) {
		$format = self::parseFormat($format, [
			self::FU => '',
			self::FFD => 1,
			self::FDS => ',',
			self::FGS => ' ',
			self::FZ => 'n/a'
		]);
		if (0 == $size && false !== $format[self::FZ]) {
			return $format[self::FZ];
		}
		$numberFormat = 'NFD=' . $format[self::FFD]
			. ';NDS=' . $format[self::FDS]
			. ';NGS=' . $format[self::FGS]
			. ';NZ=' . $format[self::FZ];
		if ($format[self::FU]) {
			$units = explode(',', $format[self::FU]);
			$i = floor(log($size, 1024));
			while (!isset($units[$i])) {
				$i--;
			}
			$size = $size / pow(1024, $i);
			return self::formatNumber($size, $numberFormat) . ' ' . $units[$i];
		} else {
			return self::formatNumber($size, $numberFormat);
		}
	}

	public static function formatNumberUnits($number, $format = null) {
		$format = self::parseFormat($format, [
			self::NUU => '',
			self::NUFD => 1,
			self::NUDS => ',',
			self::NUGS => ' ',
			self::NUZ => 'n/a'
		]);
		if (0 == $number && false !== $format[self::NUZ]) {
			return $format[self::NUZ];
		}
		$numberFormat = 'NFD=' . $format[self::NUFD]
			. ';NDS=' . $format[self::NUDS]
			. ';NGS=' . $format[self::NUGS]
			. ';NZ=' . $format[self::NUZ];
		if ($format[self::NUU]) {
			$units = explode(',', $format[self::NUU]);
			$i = floor(log($number, 1000));
			while (!isset($units[$i])) {
				$i--;
			}
			$number = $number / pow(1000, $i);
			return self::formatNumber($number, $numberFormat) . ' ' . $units[$i];
		} else {
			return self::formatNumber($number, $numberFormat);
		}
	}

	public static function formatTimeUnits($seconds, $format = null) {
		$parts = [
			'y' => [3153600, 'год,года,лет'],
			'm' => [259200, 'месяц,месяца,месяцев'],
			'd' => [8640, 'день,дня,дней'],
			'h' => [3600, 'час,часа,часов'],
			'i' => [60, 'минуту,минуты,минут'],
			's' => [0, 'секунду,секунды,секунд']
		];
		$short = true;
		$name = [];
		foreach ($parts as $k => $variants) {
			if ($parts[$k][0] >= $seconds) {
				$v = floor($parts[$k][0] / $seconds);
				$name[] = $v . ' ' . getWordDeclension($v, $parts[$k][1]);
				if ($short) {
					break;
				}
				$seconds = $seconds % $parts[$k][0];
			}
		}
		return implode(' ', $name);
	}

	private static function _formatParam($value, $options) {
		switch ($options['type']) {
			case 'text':
				break;
			case 'email':
				$options['href'] = 'mailto:' . $value;
				break;
			case 'address':
				$value = '<address>' . $value . '</address>';
				break;
			case 'phone':
				$options['href'] = 'tel:' . $value;
				//$value = preg_replace('', '', $value);
				break;
			case 'url':
				$options['href'] = $value;
				break;
			case 'enum':
				$value = self::formatEnum($value, $options['enum']);
				break;
			case 'array':
				$value = (isset($options['array'][$value]) ? $options['array'][$value] : null);
				break;
			case 'price':
				$value = self::formatNumber($value, self::PRICE_FORMAT)
					. ' <span>' . CURRENCY::getLabel(CURRENCY::getDefault()) . '</span>';
				break;
			default:
				$fn = 'format' . ucfirst($options['type']);
				if (method_exists('Format', $fn)) {
					$fnParams = [$value];
					if (isset($options['format'])) {
						$fnParams[] = $options['format'];
					}
					$value = call_user_func_array(['Format', $fn], $fnParams);
				}
		}
		if ($value) {
			if (isset($options['href'])) {
				return '<a href="' . $options['href'] . '">' . $value . '</a>';
			}
		}
		return $value;
	}

	public static function formatParams($data, $options) {
		$html = '';
		$defaultParam = ['type' => 'text', 'label' => ''];
		foreach ($options as $k => $v) {
			if (!is_array($v))
				$v = ['label' => $v];

			$v = array_merge($defaultParam, $v);
			if (!isset($data[$k]))
				continue;

			$value = $data[$k];
			if (null === $value || '' === $value)
				continue;

			$value = self::_formatParam($value, $v);
			if (null === $value)
				continue;

			//$val .= ' ' . self::_arrVal($v, 'afterText', '');
			$html .= '<tr class="' . $k . '"><th>' . $v['label'] . '</th><td>' . $value . '</td></tr>';
		}
		return ($html ? '<table class="table-params">' . $html . '</table>' : '');
	}

	public static function exec($value, $format) {
		$type = gettype($value);
		switch ($type) {
			case 'integer':
				return self::formatNumber($value, $format);
			case 'float':
			case 'double':
				return self::formatNumber($value, $format);
			case 'boolean':
				return self::formatBoolean($value, $format);
			//case 'string':
			//	return self::formatText($value, $format);
			case 'object':
				if ($value instanceof \DateTime) {
					return self::formatDate($value, $format);
				}
		}
		return $value;
	}

}