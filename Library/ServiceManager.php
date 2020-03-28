<?php
use Mvc\Application;
use Mvc\Request\Http as Request;
use Mvc\Response\Html as Response;
use Mvc\Router;

class ServiceManager{
	
	private $services = [];
	
	public function set($name, $service) {
		$this->services[strtolower($name)] = $service;
	}
	
	public function get($name) {
		$name = strtolower($name);
		if ($this->has($name))
			return $this->services[$name];
		switch ($name) {
			case 'application':
				$this->set($name, new Application($this));
				break;
			case 'request':
				$this->set($name, new Request());
				break;
			case 'response':
				$this->set($name, new Response());
				break;
			case 'router':
				$this->set($name, new Router($this->get('request')));
				break;
			default:
				return null;
		}
		return $this->get($name);
	}
	
	public function has($name) {
		return isset($this->services[$name]);
	}
	
}