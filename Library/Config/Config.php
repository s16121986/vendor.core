<?php
namespace Config;

class Config{
	
	protected $data = [];
	
	public function __construct($data = null) {
		if ($data) {
			foreach ($data as $k => $v) {
				$this->set($k, $v);
			}
		}
	}
	
	public function __set($name, $value) {
		return $this->set($name, $value);
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function set($name, $value) {
		$this->data[$name] = $value;
		return $this;
	}
	
	public function get($name, $default = null) {
		return isset($this->data[$name]) ? $this->data[$name] : $default;
	}

	public function asArray() {
		return $this->data;
	}
	
	public function isEmpty() {
		return empty($this->data);
	}
	
	public function applyFunctions(array $paths) {
		foreach ($paths as $path => $callable) {
			if (($value = $this->get($path))) {
				call_user_func($callable, $value);
			}
		}
	}
	
}