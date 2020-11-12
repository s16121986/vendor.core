<?php

namespace Mvc\Controller;

abstract class AbstractAction {

	protected $attributes = [];
	protected $uniqueId;
	protected $controller;

	protected function init() { }

	public function __construct(AbstractController $controller) {
		$this->controller = $controller;
		$this->init();
		$a = explode('\\', get_class($this));
		$this->uniqueId = strtolower(str_replace('Action', '', array_pop($a)));
	}

	public function __set($name, $value) {
		$this->attributes[$name] = $value;
	}

	public function setAttributes(array $attributes) {
		foreach ($attributes as $k => $v) {
			$this->attributes[$k] = $v;
		}
		return $this;
	}

	public function getUniqueId() {
		return $this->controller->getId() . '/' . $this->id;
	}

	public function getRequest() {
		return $this->controller->get('request');
	}

	public function getResponse() {
		return $this->controller->get('response');
	}

	abstract public function run();

}