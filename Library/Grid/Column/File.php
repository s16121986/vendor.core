<?php
namespace Grid\Column;

class File extends AbstractColumn{

	protected $_options = array(

	);

	public function formatValue($value, $row = null) {
		if ($value) {
			return '<a href="/file/' . $value . '/" target="_blank">скачать</a>';
		}
		return '';
	}
}