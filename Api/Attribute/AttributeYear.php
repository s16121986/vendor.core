<?php
namespace Api\Attribute;

class AttributeYear extends AbstractAttribute{

	protected $qualifiers = [
		'yearRange' => 100
	];

	public function checkValue($value) {
		return parent::checkValue($value) && preg_match('/^[1-9]\d{3}$/', (string)$value);
	}

	public function prepareValue($value) {
		return (int)$value;
	}

}