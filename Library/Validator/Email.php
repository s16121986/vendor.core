<?php
namespace Validator;

class Email extends AbstractValidator{
	
	public function isValid($value) {
		return '' === $value || (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
	}
	
}