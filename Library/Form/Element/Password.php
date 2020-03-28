<?php
namespace Form\Element;

class Password extends Text{

	protected $_options = array(
		'inputType' => 'password'
	);

	public function checkValue($value) {
		return !empty($value);
	}

}
