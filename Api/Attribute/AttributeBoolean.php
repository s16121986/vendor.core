<?php
namespace Api\Attribute;

class AttributeBoolean extends AbstractAttribute{

	public function prepareValue($value) {
		return (bool)$value;
	}

	public function getPresentation() {
		return \Format::formatBoolean($this->value, '');
	}

}