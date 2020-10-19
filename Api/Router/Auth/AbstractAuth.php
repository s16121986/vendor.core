<?php

namespace Api\Router\Auth;

abstract class AbstractAuth {

	protected $config = [];

	public static function factory($config) {
		$name = $config['type'];
		$cls = __NAMESPACE__ . '\\' . ucfirst($name);
		unset($config['type']);
		return new $cls($config);
	}

	public function __construct($config) {
		$this->config = $config;
	}

	public function __get($name) {
		return isset($this->config[$name]) ? $this->config[$name] : null;
	}

	abstract public function authorization();

}