<?php
namespace Api\Attribute;

use Enum\Rules;

class AttributeEnum extends AbstractAttribute{

	private $rules = null;
	protected $qualifiers = [
		'enum' => null
	];

	public function checkValue($value) {
		if (parent::checkValue($value)) {
			if (call_user_func(['\\' . $this->enum, 'valueExists'], $value) || call_user_func(['\\' . $this->enum, 'keyExists'], $value)) {
				if ($this->rules) {
					return $this->rules->isValid($this->value, $value);
				}
				return true;
			}
		}
		return false;
	}

	public function prepareValue($value) {
		$cls = '\\' . $this->enum;
		if (!call_user_func([$cls, 'valueExists'], $value)) {
			if (is_string($value) && preg_match('/^[a-z_]+$/i', $value)) {
				$value = call_user_func([$cls, 'getValue'], $value);
			} else {
				return null;
			}
		}
		return $value;
	}

	public function getDefault() {
		if ($this->notnull) {
			return call_user_func(['\\' . $this->enum, 'getDefault']);
		}
		return null;
	}

	public function getPresentation() {
		return call_user_func(['\\' . $this->enum, 'getLabel'], $this->value);
	}
	
	public function addRule($value, $rules, $role = null) {
		$this->getRules()->add($value, $rules, $role);
		return $this;
	}
	
	public function getRules() {
		if (null === $this->rules) {
			$this->rules = new Rules();
		}
		return $this->rules;
	}

}