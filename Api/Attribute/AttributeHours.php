<?php
namespace Api\Attribute;

class AttributeHours extends AbstractAttribute{

	public static function timeToInt($time) {
		if (is_int($time)) {
			$i = $time;
		} elseif (is_string($time)) {
			$i = 0;
			$x = explode(':', $time);
			if (isset($x[1])) {
				$i += (int) array_shift($x) * 60;
			}
			$i += (int) $x[0];
			unset($x);
		} else {
			return 0;
		}
		if ($i > 0) {
			return $i;
		}
		return 0;
	}

	public static function intToTime($int) {
		$s = str_pad(floor($int / 60), 2, '0', STR_PAD_LEFT);
		$s .= ':' . str_pad($int % 60, 2, '0', STR_PAD_LEFT);
		return $s;
	}

	public function prepareValue($value) {
		return self::timeToInt($value);
	}

}