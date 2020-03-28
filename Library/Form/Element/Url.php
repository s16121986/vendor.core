<?php
namespace Form\Element;

class Url extends Text{

	protected $_options = array(
		'inputType' => 'url'
	);

	public function checkValue($value) {
		return '' === $value || (bool)filter_var($value, FILTER_VALIDATE_URL);
	}


}
