<?php
namespace Mvc;

use ServiceManager;
use Exception;
use Error;
use ErrorException;
use Exception as BaseException;
use Http\Response as HttpResponse;
use Config\Config;

class Application{
	
	protected $serviceManager;
	protected $request;
	protected $response;
	protected $applicationPath;
	protected $applicationNamespace;
	protected $configuration;
	
	public static function init(array $config) {
		$serviceManager = new ServiceManager();
		$serviceManager->set('ApplicationConfig', new Config($config));
		$cls = get_called_class();
		$application = new $cls($serviceManager);
		return $application;
	}
	
	public function __construct($serviceManager, $request = null, $response = null) {
		//$serviceManager->set('Application', $this);
		$this->serviceManager = $serviceManager;
		$this->request = $request ?: $serviceManager->get('request');
		$this->response = $response ?: $serviceManager->get('response');
		$this->applicationPath = $serviceManager->get('ApplicationConfig')->get('applicationPath');
		$this->applicationNamespace = $serviceManager->get('ApplicationConfig')->get('applicationNamespace');
	}
	
	public function get($name) {
		switch ($name) {
			case 'applicationPath':
				return $this->$name;
			case 'request':
				return $this->request;
			case 'response':
				return $this->response;
		}
		if ($this->serviceManager->has($name))
			return $this->serviceManager->get($name);
		return null;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	public function run() {
		
		$router = $this->serviceManager->get('router');
		
		//$this->loadController('InitController');
		
		try {
			$this->trigger('route');
			
			$controllerName = $router->getControllerName();
			$controller = $this->getController($controllerName);
			if ($controller && $controllerName !== 'index') {
				if (!$router->applyController($controller))
					$controller = null;
			}
			
			if ($controller) {
				$actionName = $router->getActionName();
			} else {
				$actionName = $controllerName;
				$controllerName = 'index';
				
				$this->request->setControllerName($controllerName);
				
				$controller = $this->getController($controllerName);
				if (method_exists($controller, $actionName . 'Action')) {
					
				} else if (!method_exists($controller, 'indexAction'))
					throw new Exception('Action not found', 404);
				else
					$actionName = 'index';
				
				$this->request->setActionName($actionName);
				//$router->applyController($controller);
			}
			
			$this->trigger('dispatch');
			$return = $controller->{$actionName . 'Action'}();
			
		} catch (BaseException | ErrorException | Error $e) {
			ob_get_clean();
			$this->response->setException($e);
			$return = $this->runController('error', 'error');
			//throw $e;
		}
		if ($return instanceof HttpResponse)
			$this->response = $return;
		else if (null !== $return)
			$this->response->setContent($return);
		return $this->response;
	}
	
	public function getController($controllerName) {
		$controllerClass = $this->getControllerClass($controllerName);
		//$cls = 'Application\Controller\\' . $controllerClass;
		if (!class_exists($controllerClass, true)) {
			//if (!$this->loadController($controllerClass))
				return null;
		}
		return new $controllerClass($this);
	}
	
	public function runController($controllerName, $actionName) {
		$controller = $this->getController($controllerName);
		if (!$controller)
			throw new Exception('Controller not found (' . $controllerName . ')', 404);
		return $controller->{$actionName . 'Action'}();
	}
	
	protected function getControllerClass($controllerName) {
		return '\\' . $this->applicationNamespace . '\Controller\\' . ucfirst($controllerName) . 'Controller';
	}
	
	/*protected function loadController($controllerClass) {
		$filename = $this->applicationPath . '/Controller/' . $controllerClass . '.php';
		if (!file_exists($filename))
			return false;
		include $filename;
		return true;
	}*/
	
	protected function trigger($event) {
		
	}
	
}