<?php
namespace Api\Util\Settings\Collection;

class Item{
	
	const nameRegExp = '[a-z_][a-z0-9_]*';

	protected $params = [
		'name' => null
	];

	public function __set($name, $value) {
		if (method_exists($this, 'set' . $name)) {
			return $this->{'set' . $name}($value);
		}
		if (array_key_exists($name, $this->params)) {
			$this->_set($name, $value);
		}
	}

	public function __get($name) {
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return null;
	}

	protected function _set($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}

	protected function init($params) {
		if (is_array($params)) {
			foreach ($params as $k => $v) {
				$this->$k = $v;
			}
		}
		return $this;
	}

	public function setName($name) {
		$this->_set('name', trim($name));
		return $this;
	}

	public function reset() {
		foreach ($this->params as $k => $v) {
			$this->params[$k] = null;
		}
		return $this;
	}

}