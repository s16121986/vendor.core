<?php
namespace Validator;

class GeoCoord extends AbstractValidator{
	
	const COORD_REGEXP = '-?\d{1,3}\.\d+';
	
	public function isValid($value, $point = true) {
		if ('' === $value) return true;
		if (!is_string($value)) return false;
		if ($point) {
			return preg_match('/^' . self::COORD_REGEXP . ',' . self::COORD_REGEXP . '$/', $value);
		} else {
			return preg_match('/^' . self::COORD_REGEXP . '$/', $value);
		}
	}
	
}