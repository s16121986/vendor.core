<?php
namespace Api\Router;

class Param{
	
	protected static $defaultRegExp = '.+';
	
	protected $name = null;
	protected $handler = null;
	protected $value = null;
	
	public function __construct($name = null, $handler = null) {
		$this->name = $name;
		$this->handler = $handler;
	}
	
	public function __get($name) {
		return (isset($this->$name) ? $this->$name : null);
	}
	
	public function getRegExp() {
		return (is_string($this->handler) ? $this->handler : self::$defaultRegExp);
	}
	
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}
	
	public function callback($request, $response) {
		if (is_callable($this->handler)) {
			return call_user_func($this->handler, $request, $response);
		}
	}
	
	public function __toString() {
		return (string)$this->value;
	}
	
}