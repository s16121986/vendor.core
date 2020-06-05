<?php
namespace Api\Attribute;

use Api\Exception;
use Api\Attribute\Exception as AttributeException;

abstract class AbstractAttribute{

	protected static $defaultParams = [
		'primary' => false,
		'required' => false,
		'notnull' => true,
		'default' => null,
		'locale' => false,

		'dbfield' => true,

		'filterable' => true,
		'sortable' => true,
		'hidden' => false,

		'changeable' => true,

		'autoPrepare' => true
	];

	protected $name = '';
	protected $data = [];
	protected $qualifiers = [];
	protected $validators = [];
	protected $value = null;
	protected $changed = false;

	public function __construct($name, $qualifiers = null) {
		$this->name = $name;
		$this->qualifiers = array_merge(self::$defaultParams, $this->qualifiers);
		if ($qualifiers)
			$this->setQualifiers($qualifiers);
	}

	public function __get($name) {
		switch($name) {
			case 'name':
			case 'changed': return $this->$name;
			case 'value': return $this->getValue();
		}
		
		if (isset($this->qualifiers[$name]))
			return $this->qualifiers[$name];
		
		if (isset($this->data[$name]))
			return $this->data[$name];
		
		return null;
	}

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function addValidator($validator, $options = null) {
		if (is_string($validator)) {
			$cls = '\\Validator\\' . $validator;
			$validator = new $cls($options);
		}
		$this->validators[] = $validator;
		return $this;
	}

	public function setQualifiers($qualifiers) {
		foreach ($qualifiers as $k => $v) {
			if (array_key_exists($k, $this->qualifiers)) {
				$this->qualifiers[$k] = $v;
			}
		}
		return $this;
	}
	
	public function getQualifiers() {
		return $this->qualifiers;
	}

	public function checkValue($value) {
		foreach ($this->validators as $validator) {
			if (!$validator->isValid($value))
				return false;
		}
		return true;
	}

	public function prepareValue($value) {
		return $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($value, $forced = false) {
		//if (!$this->changeable)
		//	throw new AttributeException(Exception::ATTRIBUTE_NOT_CHANGEABLE, $this->name);
		if ($forced) {
			$this->value = $this->prepareValue($value);
			return true;
		}
		
		if ($value === $this->value)
			return true;
		
		if (null !== $value) {
			if (false === $this->checkValue($value))
				return false;

			if ($this->autoPrepare)
				$value = $this->prepareValue($value);
		}
		
		if (null === $value && true === $this->notnull)
			return false;
		
		$this->setChanged(true);
		$this->value = $value;
		return true;
	}
	
	public function setChanged(bool $flag) {
		$this->changed = $flag;
		return $this;
	}

	public function setDefault() {
		return $this->setValue($this->default);
	}
	
	public function reset() {
		$this->value = null;
		$this->changed = false;
	}
	
	public function isEmpty() {
		return (null === $this->value);
	}

	public function getType() {
		return \AttributeType::getValue(str_replace(__NAMESPACE__ . '\Attribute', '', get_class($this)));
	}

	public function getPresentation() {
		return (string)$this->value;
	}

	public function __toString() {
		return $this->getPresentation();
	}

}