<?php
namespace Grid\Column;

use Translation\Calendar;

class Month extends AbstractColumn{

	protected $_options = array(
		'icon' => false
	);

	public function formatValue($value, $row = null) {
		return Calendar::getMonth($value);
	}

}