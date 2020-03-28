<?php
namespace Api\Attribute;

use Auth;

class AttributePassword extends AttributeString{

	protected $qualifiers = [
		'hidden' => true,
		'regexp' => false,
		'length' => 32
	];

	/*public function checkValue($value) {
		return parent::checkValue($value) && \Auth\Util::checkPassword($value);
	}*/

	public function checkValue($value) {
		return parent::checkValue($value) && (!$this->regexp || preg_match($this->regexp, $value));
	}

	public function prepareValue($value) {
		$value = parent::prepareValue($value);
		return Auth::getUser()->getPassword()->encrypt($value);
	}

}