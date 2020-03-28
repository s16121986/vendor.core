<?php
namespace Grid\Column;

class Boolean extends AbstractColumn{

	protected $_options = array(
		'trueText' => 'да',
		'falseText' => 'нет'
	);

	public function formatValue($value, $row = null) {
		$value = (bool)$value;
		return ($value ? $this->trueText : $this->falseText);
	}

	public function render($value, $row) {
		$s = parent::render($value, $row);
		return '<span class="boolean-' . ($value ? 'true' : 'false') . '">' . $s . '</span>';
	}

}