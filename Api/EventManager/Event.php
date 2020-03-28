<?php
namespace Api\EventManager;

class Event{
	
	private $action;
	private $api;
	private $data = array();
	
	public function __construct($action) {
		$this->action = $action;
	}
	
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		}
		return (isset($this->data[$name]) ? $this->data[$name] : null);
	}
	
	public function setData($data) {
		if (is_array($data)) {
			$this->data = $data;
		}
		return $this;
	}
	
	public function setApi($api) {
		$this->api = $api;
		return $this;
	}
	
}