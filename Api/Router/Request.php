<?php
namespace Api\Router;

class Request{
	
	protected $route;
	private $_request = null;
	
	public function __construct($route) {
		$this->route = $route;
	}
	
	public function __get($name) {
		$this->getData();
		return (isset($this->_request[$name]) ? $this->_request[$name] : null);
	}
	
	public function getData() {
		if (null === $this->_request) {
			$this->_request = $_REQUEST;
			//$this->_request = self::urldecode($_REQUEST);
		}
		return $this->_request;
	}
	
	public function getPath() {
		$uri = $_SERVER['REQUEST_URI'];
		if (false !== ($p = strpos($uri, '?'))) {
			$uri = substr($uri, 0, $p);
		}
		if ($this->route->basePath) {
			$uri = str_replace($this->route->basePath, '', $uri);
		}
		return $uri;
	}
	
	public function getMethod() {
		return (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null);
	}
	
	private static function urldecode($data) {
		if (is_array($data)) {
			$temp = $data;
			$data = array();
			foreach ($temp as $k => $v) {
				$data[$k] = self::urldecode($v);
			}
		} elseif (is_string($data)) {
			return urldecode($data);
		}
		return $data;
	}
	
}