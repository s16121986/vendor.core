<?php
namespace Grid\Column;

use Stdlib\DateTime;

class Date extends AbstractColumn{

	protected $_options = array(
		'format' => 'd.m.Y'
	);

	public function formatValue($value, $row = null) {
		$t = strtotime($value);
		if ($t > 0) {
			$d = DateTime::factory($t)->format($this->format);
		} else {
			$d = '';
		}
		return parent::formatValue($d, $row);
	}

}