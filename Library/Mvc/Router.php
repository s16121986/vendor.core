<?php
namespace Mvc;

class Router{
	
	protected $request;
	protected $requestPath;
	protected $controllerPathIndex = 0;
	
	public function __construct($request) {
		$this->setRequest($request);
	}
	
	public function setRequest($request) {
		$this->request = $request;
		//var_dump($path);
	}
	
	public function getControllerName() {
		$name = $this->request->getControllerName();
		if (null === $name) {
			$name = $this->getPath($this->controllerPathIndex) ?: 'index';
			$this->request->setControllerName($name);
		}
		return $name;
	}
	
	public function getActionName() {
		$name = $this->request->getActionName();
		if (null === $name) {
			$name = $this->getPath($this->controllerPathIndex + 1) ?: 'index';
			$this->request->setActionName($name);
		}
		return $name;
	}
	
	public function applyController(Controller $controller) {
		foreach ($controller->getRoutes() as $route) {
			if (false !== ($params = $this->_route($route))) {
				foreach ($params as $n => $v) {
					$controller->$n = $v;
				}
				return true;
			}
		}
		$actionName = $this->getActionName();
		$pathCount = count($this->requestPath);
		if ($pathCount === 1 && $actionName === 'index')
			return true;
		if ($pathCount === 2 && method_exists($controller, $actionName . 'Action'))
			return true;/**/
		return false;
	}
	
	public function getPath($index = null) {
		if (null === $this->requestPath) {
			$path = explode('/', trim($this->request->getPath(), '/'));
			//array_shift($path);
			$this->requestPath = $path;
		}
		if (null === $index)
			return $this->requestPath;
		else
			return isset($this->requestPath[$index]) ? $this->requestPath[$index] : null;
	}
	
	public function shiftPath() {
		array_shift($this->requestPath);
	}

	protected function _route($route) {
		$routePaths = explode('/', trim($route[0], '/'));
		//unset($routePaths[count($routePaths) - 1], $routePaths[0]);
		$pathIndex = $this->controllerPathIndex + 1;
		$pathCount = count($this->requestPath) - $pathIndex;
		if ($pathCount !== count($routePaths))
			return false;
		
		$routeVars = [];
		for ($i = 0; $i < $pathCount; $i++) {
			$requestPath = $this->requestPath[$i + $pathIndex];
			if (false === ($vars = $this->_routePath($requestPath, $routePaths[$i], $route)))
				return false;
			$routeVars = array_merge($routeVars, $vars);
		}
		$params = $routeVars;
		
		if (is_array($route[1])) {
			foreach ($route[1] as $n => $v) {
				switch ($n) {
					case 'action':
						$this->request->setActionName($v);
						break;
					default:
						$params[$n] = $v;
				}
			}
		}
		
		return $params;
	}
	
	protected function _routePath($requestPath, $routePath, $route) {
		$vars = explode(':', $routePath);
		$config = $route[2];
		$reg = array_shift($vars);
		if (count($vars) > 0) {
			foreach ($vars as $var) {
				if (isset($config[$var])) {
					$reg .= '(' . $config[$var] . ')';
				} else {
					$reg .= '(.+?)';
				}
			}
		}
		preg_match('/^' . $reg . '$/', $requestPath, $m);
		if ($m) {
			$routeVars = [];
			foreach ($vars as $i => $var) {
				$routeVars[$var] = $m[$i + 1];
			}
			return $routeVars;
		}
		return false;
	}
	
}
