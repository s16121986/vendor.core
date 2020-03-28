<?php
namespace Menu\Util;

abstract class AbstractItem{
	
	protected $parent = null;
	protected $options = array();
	
	public function __construct($options = array()) {
		if (is_array($options)) {
			$this->setOptions($options);
		}
		$this->init();
	}
	
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		} elseif (method_exists($this, 'get' . $name)) {
			return $this->{'get' . $name}();
		}
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}
	
	public function __set($name, $value) {
		if (method_exists($this, 'set' . ucfirst($name))) {
			$this->{'set' . $name}($value);
		} else {
			$this->options[$name] = $value;
		}
	}
	
	public function setOptions($options) {
		foreach ($options as $k => $v) {
			$this->__set($k, $v);
		}
	}
	
	public function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}
	
	public function getParent() {
		return $this->parent;
	}
	
	public function getRoot() {
		return ($this->parent ? $this->parent->getRoot() : $this);
	}
	
	protected function init() {}
	
}