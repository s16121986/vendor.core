<?php

namespace Api\Router;

use Api\Router\Auth\AbstractAuth as AuthFacory;
use Api\Exception;
use Exception as BaseException;

class Handler {

	protected $request;
	protected $response;
	protected $pluginManager;
	protected $paths = [];
	protected $params = [];
	protected $handlers = [];
	protected $options = [
		'basePath' => '/api',
		//'next'
	];
	private $current = null;

	public static function factory($options = null) {
		return new self($options);
	}

	public function __construct($options = null) {
		$this->pluginManager = new PluginManager();
		$this->request = new Request($this);
		$this->response = new Response();

		if (is_array($options)) {
			if (isset($options['auth'])) {
				$this->pluginManager->add('auth', AuthFacory::factory($options['auth']));
				unset($options['auth']);
			}

			foreach ($options as $k => $v) {
				$this->$k = $v;
			}
		}

		if ($this->controllerPath)
			ControllerFactory::setup($this->controllerPath, $this->controllerNamespace);
	}

	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}

	public function __set($name, $value) {
		$this->options[$name] = $value;
	}

	public function execute() {
		$authPlugin = $this->pluginManager->get('auth');
		if ($authPlugin && false === $authPlugin->authorization()) {
			$this->response->setHttpError(403, 'Access denied');
			$this->response->send();
			return;
		}

		try {
			if (null === $this->next())
				$this->response->setHttpError(404, 'Action not found');
		} catch (BaseException | \Error $e) {
			$this->response->setException($e);
		}

		$this->response->send();
	}

	public function next() {
		if (null === $this->current) {
			$this->current = 0;
		} else {
			$this->current++;
		}
		if (isset($this->paths[$this->current])) {
			$path = $this->paths[$this->current];
			if (null !== $path->query())
				return true;

			return $this->next();
		}

	}

	public function param($name, $handler = null) {
		$this->params[$name] = new Param($name, $handler);
		return $this;
	}

	public function getParam($name) {
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}

	public function bind($event, $callback) {
		$this->handlers[] = [$event, $callback];
		return $this;
	}

	public function all() {
		return $this->_path('all', func_get_args());
	}

	public function get() {
		return $this->_path('get', func_get_args());
	}

	public function post() {
		return $this->_path('post', func_get_args());
	}

	public function put() {
		return $this->_path('put', func_get_args());
	}

	public function delete() {
		return $this->_path('delete', func_get_args());
	}

	public function head() {
		return $this->_path('head', func_get_args());
	}

	protected function trigger($event) {
		foreach ($this->handlers as $handler) {
			if ($handler[0] !== $event)
				continue;
			return call_user_func($handler[1], $this);
		}
	}

	private function _path($method, $args) {
		$request = new Path($this, $method, $args);
		$this->paths[] = $request;
		return $this;
	}

}