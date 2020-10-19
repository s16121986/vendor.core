<?php

namespace Api\Router;

use Api;
use Auth;
use Stdlib\DateTime;
use Exception;
use Api\Service\Log;
use App;

class Path {

	protected $route = null;
	protected $method = 'all';
	protected $path = '';
	protected $callback = [];
	protected $params = [];
	protected $data = [];

	public function __construct($route, $method, $args) {
		$this->route = $route;
		$this->method = strtolower($method);
		$this->path = array_shift($args);
		$this->callback = $args;
	}

	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return (isset($this->$name) ? $this->$name : null);
	}

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function setParams($params) {
		$this->params = $params;
		return $this;
	}

	public function hasMethod($method) {
		return ($this->method === 'all' || $this->method === strtolower($method));
	}

	public function getRequest() {
		return $this->route->request;
	}

	public function getResponse() {
		return $this->route->response;
	}

	public function callback() {
		$response = $this->route->response;

		foreach ($this->params as $param) {
			$param->callback($this, $response);
		}

		foreach ($this->callback as $fn) {
			$result = null;
			if (is_string($fn)) {
				$a = explode('/', $fn);
				if (isset($a[1])) {
					$result = ControllerFactory::callRoute($a[0], $a[1], $this);
				} else if (method_exists($this, 'action_' . $fn)) {
					$result = $this->{'action_' . $fn}();
				} else if (function_exists($fn)) {
					$result = $fn($this);
				} else {
					throw new Exception('function "' . $fn . '" undefined');
				}
			} else if (is_callable($fn)) {
				$result = call_user_func($fn, $this);
			}
			if (null !== $result) {
				$response->set($result);
				return true;
			}
		}

		return null;
	}

	public function query() {
		$request = $this->getRequest();
		$requestPath = $request->getPath();
		$method = $request->getMethod();
		if (!$this->hasMethod($method))
			return null;

		if ($this->path === '*') {

		} else {
			$params = [];
			$requestPaths = explode('/', $requestPath);
			$paths = explode('/', $this->path);
			foreach ($paths as $i => $part) {
				if (isset($requestPaths[$i])) {
					if ('*' === $part && $requestPaths[$i]) {
						continue;
					}
					if (0 === strpos($part, ':')) {
						$part = substr($part, 1);
						if (!($param = $this->route->getParam($part))) {
							$param = new Param($part);
						}
					} else if ($part === $requestPaths[$i]) {
						continue;
					} else {
						return null;
						//$param = new Param();
					}
					if (preg_match('/^' . $param->getRegExp() . '$/', $requestPaths[$i])) {
						$param->setValue($requestPaths[$i]);
						if ($param->name) {
							$params[$param->name] = $param;
						} else {
							$params[] = $param;
						}
						continue;
					}
				} else {
					return null;
				}
			}
			$this->setParams($params);
		}
		return $this->callback();
	}

	private function action_login() {
		$auth = self::getAuth();
		//return $result->getData();
		if (!$auth->login($this->getRequest()->getData())) {
			throw new Exception('Invalid data', 403);
		}
		return [
			'token' => $auth->getToken()->token,
			'user_id' => UserId
		];
	}

	private function action_logout() {
		$auth = self::getAuth();
		if ($auth->authentication()) {
			$auth->logout();
		}
		return true;
	}

	private function action_datetime() {
		return [
			'datetime' => DateTime::serverDatetime()
		];
	}

	private function action_authentication() {
		$auth = self::getAuth();
		if (!$auth->authentication()) {
			$this->getResponse()->setHttpCode(401);
			throw new Exception('Authentication failed', 401);
		}

		App::setUserId(UserId);
	}

	private function action_model() {
		$request = $this->getRequest();
		$path = $request->getPath();
		$path = trim($path, '/');
		$parts = explode('/', $path);
		$cls = [];
		foreach ($parts as $part) {
			$cls[] = ucfirst($part);
		}
		if (count($cls) < 2) {
			return false;
		}
		$action = array_pop($cls);
		$model = implode('\\', $cls);
		$api = Api::factory($model);
		$data = $request->getData();
		if (isset($data['id']) && !in_array($action, ['select', 'count'])) {
			if ($api->setId($data['id'])) {
				unset($data['id']);
			}
		}
		return $api->$action($data);
	}

	private function action_log() {
		Log::exec();
	}

	private static function getAuth() {
		if (!($auth = Auth::getUser())) {
			$auth = Auth::factory([
				'storage' => 'Headers'
			]);
		}
		return $auth;
	}

}
