<?php
namespace Api\Attribute;

use Api\Attribute\Exception;

class AttributePredefined extends AbstractAttribute{

	protected $qualifiers = [
		'value' => null
	];
	protected $changed = true;

	public function prepareValue($value) {
		return $this->qualifiers['value'];
	}

	public function getDefault() {
		return $this->qualifiers['value'];
	}
	
	
	public function getValue() {
		return $this->qualifiers['value'];
	}

	public function setValue($value, $forced = false) {
		//throw new Exception(Exception::ATTRIBUTE_PREDEFINED, $this->name);
	}

}