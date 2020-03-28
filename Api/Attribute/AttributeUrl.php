<?php
namespace Api\Attribute;

class AttributeUrl extends AbstractAttribute{

	public function checkValue($value) {
		return (parent::checkValue($value) && ('' === $value || (bool)filter_var($value, FILTER_VALIDATE_URL)));
	}
	
	public function prepareValue($value) {
		if ('' === $value) {
			return null;
		}
		return parent::prepareValue($value);
	}

}