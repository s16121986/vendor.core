<?php
namespace Grid\Column;

class Number extends AbstractColumn{

	protected $_options = array(
		'format' => 'NFD=2;NDS=,;NGS= '
	);

	public function prepareValue($value) {
		if (self::isNullValue($value)) {
			return null;
		}
		return (float)$value;
	}

	public function formatValue($value, $row = null) {
		return ($this->format ? \Format::formatNumber($value, $this->format) : $value);
	}
	
	private static function isNullValue($value) {
		return ('' === $value || null === $value);
	}

}