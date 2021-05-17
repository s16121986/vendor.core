<?php

namespace Mvc\Controller;

use Mvc\Application;

abstract class AbstractController {

	protected $uniqueId;
	protected $application;
	protected $data = [];

	protected function init() { }

	public function __construct(Application $application) {
		$this->application = $application;
		$a = explode('\\', get_class($this));
		$this->uniqueId = strtolower(str_replace('Controller', '', array_pop($a)));
		$this->init();
	}

	public function __get($name) {
		switch ($name) {
			case 'application':
				return $this->$name;
		}

		return $this->get($name);
	}

	public function __set($name, $value) {
		$this->set($name, $value);
	}

	public function getId() {
		return $this->uniqueId;
	}

	public function get($name) {
		if ($this->has($name))
			return $this->data[$name];
		return $this->application->get($name);
	}

	public function set($name, $value) {
		$this->data[$name] = $value;
	}

	public function has($name) {
		return array_key_exists($name, $this->data);
	}

	public function getRequest() {
		return $this->application->get('request');
	}

	public function getResponse() {
		return $this->application->get('response');
	}

	public function onDispatch() {

	}

	public function trigger($event) {

	}

}