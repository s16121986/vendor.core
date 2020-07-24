<?php

namespace Api\Attribute;

use Auth;

class AttributePassword extends AttributeString {

	protected $qualifiers = [
		'hidden' => true,
		'regexp' => false,
		'length' => 32
	];
	private $decryptedValue;

	public function checkValue($value) {
		return parent::checkValue($value) && (!$this->regexp || preg_match($this->regexp, $value));
	}

	public function prepareValue($value) {
		$value = parent::prepareValue($value);
		return Auth::getUser()->getPassword()->encrypt($value);
	}

	public function getDecryptedValue() {
		return $this->decryptedValue;
	}

	public function setValue($value, $forced = false) {
		if (!parent::setValue($value, $forced))
			return false;

		$this->decryptedValue = $value;

		return true;
	}

}
