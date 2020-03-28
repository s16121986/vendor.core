<?php
namespace Form\Element;

class Hidden extends Text{

	protected $_options = array(
		'inputType' => 'hidden'
	);

	protected function prepareValue($value) {
		//if (is_array($value)) {
		//	$value = implode(',', $value);
		//}
		return $value;
	}

}
