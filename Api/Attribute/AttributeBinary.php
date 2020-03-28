<?php
namespace Api\Attribute;

class AttributeBinary extends AbstractAttribute{

	protected $qualifiers = [
	];

	public function prepareValue($value) {
		if (is_int($value))
			return decbin($value);
		
		return $value;
	}

}