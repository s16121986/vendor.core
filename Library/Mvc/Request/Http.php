<?php
namespace Mvc\Request;

use Http\Request as HttpRequest;

class Http extends HttpRequest{
	
	protected $controllerName;
	protected $actionName;
	
	public function setControllerName($name) {
		$this->controllerName = $name;
		return $this;
	}
	
	public function getControllerName() {
		return $this->controllerName;
	}
	
	public function setActionName($name) {
		$this->actionName = $name;
		return $this;
	}
	
	public function getActionName() {
		return $this->actionName;
	}
	
}