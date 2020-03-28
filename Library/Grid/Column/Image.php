<?php
namespace Grid\Column;

class Image extends AbstractColumn{

	protected $_options = array(

	);

	public function formatValue($value, $row = null) {
		if ($value) {
			return '<img src="/file/' . $value . '/" alt="" />';
		}
		return '';
	}
}