<?php
namespace Enum;

class Rules{
	
	protected $config = array();
	
	public function add($value, $rules, $role = null) {
		if (!is_array($rules)) {
			$rules = func_get_args();
			array_shift($rules);
			$role = null;
		}
		if (!isset($this->config[$role])) {
			$this->config[$role] = array();
		}
		if (is_string($rules)) {
			$rules = explode(',', $rules);
		} elseif (!is_array($rules)) {
			$rules = array($rules);
		}
		$this->config[$role][$value] = $rules;
		return $this;
	}
	
	public function getAvailableValues($value, $role = null) {
		if (isset($this->config[$role][$value])) {
			return $this->config[$role][$value];
		}
		return array();
	}
	
	public function isValid($currentValue, $newValue, $role = null) {
		return in_array($newValue, $this->getAvailableValues($currentValue, $role));
	}
	
}