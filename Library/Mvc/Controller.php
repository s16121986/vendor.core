<?php
namespace Mvc;

use Exception;

abstract class Controller{
	
	protected $routes = [
		//['/action/path/:var/', ['action' => ''], ['var' => 'regexp']]
		//['/view/:id/', [], ['id' => '\d+']]
	];
	protected $application;
	protected $data = [];
	protected $url;
	protected $view;
	
	protected function init() {}
	
	public function __construct(Application $application) {
		$this->application = $application;
		$this->url = new Url();
		$this->view = new View($this);
		$this->init();
	}
	
	public function __get($name) {
		return $this->get($name);
	}
	
	public function __set($name, $value) {
		$this->set($name, $value);
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
	
	public function getRoutes() {
		return $this->routes;
	}
	
	public function url($uri = null, $params = null) {
		$url = clone $this->url;
		$url->query = null;
		if ($uri)
			$url->parse($uri);
		
		return $url->toString();
	}
	
	public function redirect($url = null, $code = 301) {
		if (is_int($url)) {
			$code = $url;
			$url = null;
		} else {
			$url = $this->url($url);
		}
		switch ($code) {
			case 403:
			case 404:
				throw new Exception('Not found', $code);
		}
		$this->response->setRedirect($url, $code);
	}
	
	public function render($path, array $attributes = []) {
		return $this->view->render($path, $attributes);
	}
	
	public function onDispatch() {
		
	}
	
}