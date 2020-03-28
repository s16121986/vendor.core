<?php
namespace Api\Attribute;

class AttributeString extends AbstractAttribute{

	protected $qualifiers = [
		'length' => 0,
		'minlength' => 0
	];
	
	public function checkValue($value) {
		if (is_scalar($value)) {
			$value = (string)$value;
		}
		return (is_string($value) 
			&& parent::checkValue($value)
			&& (strlen($value) >= $this->minlength || ('' === $value && false === $this->notnull)) 
			&& !($this->required && empty($value)));
	}

	public function prepareValue($value) {
		//$value = trim(filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW));
		$value = (string)$value;
		if ($this->length) {
			$value = mb_substr($value, 0, $this->length, 'utf-8');
		}
		return $value;
	}

}