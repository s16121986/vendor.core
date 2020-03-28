<?php
namespace Translation;

class Language{
	
	private $params = array(
		'name' => '',
		'code' => '',
		'locale' => '',
		'dateFormat' => '',
		'timeFormat' => '',
		'default' => false
	);
	
	public function __construct($params) {
		foreach ($params as $k => $v) {
			$this->$k = $v;
		}
	}
	
	public function __get($name) {
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}
	
	public function __set($name, $value) {
		$this->params[$name] = $value;
	}
	
	public function getLocaleCode() {
		$code = $this->locale;
		if (($p = strpos($code, '.'))) {
			$code = substr($code, 0, $p);
		}
		return $code;
	}
	
	public function formatDate($date) {
		
	}
	
	public function formatTime($date) {
		
	}
	
}