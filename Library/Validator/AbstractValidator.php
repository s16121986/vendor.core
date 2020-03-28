<?php
namespace Validator;

abstract class AbstractValidator{

	protected $_options = array();

	public function __construct($options = null) {
		if ($options) {
			$this->_options = $options;
		}
	}

	public function __get($name) {
		return (isset($this->_options[$name]) ? $this->_options[$name] : null);
	}

	abstract public function isValid($value);

}