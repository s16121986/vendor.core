<?php
namespace Form\Element;

class Email extends Text{

	protected $_options = array(
		'inputType' => 'email'
	);

	public function checkValue($value) {
		return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
	}


}
