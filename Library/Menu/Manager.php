<?php
namespace Menu;

class Manager{
	
	private $menu;
	private $currentRules = array();
	private $api = null;
	private $templates = array();
	private $items = array();
	
	private static function match($rules, $rulesRequired) {
		foreach ($rulesRequired as $k => $v) {
			if (!(isset($rules[$k]) && $v === $rules[$k])) {
				return false;
			}
		}
		return true;
	}
	
	private static function rulesFromArgs($args) {
		$rules = array();
		foreach ($args as $arg) {
			$rules[$arg] = true;
		}
		return $rules;
	}
	
	public function __construct($menu) {
		$this->menu = $menu;
	}
	
	public function setApi($api) {
		$this->api = $api;
		return $this;
	}
	
	public function setRules($rules) {
		if (is_string($rules)) {
			$args = func_get_args();
			$rules = self::rulesFromArgs($args);
		}		
		$this->currentRules = $rules;
		return $this;
	}
	
	public function add($item, $rules = null) {
		if (null === $rules) {
			$rules = array();
		} elseif (is_string($rules)) {
			$args = func_get_args();
			array_shift($args);
			$rules = self::rulesFromArgs($args);
		}
		$rules = array_merge(array(), $this->currentRules, $rules);
		if (is_string($item) && isset($this->templates[$item])) {
			$item = $this->templates[$item];
		}
		$this->items[] = array($item, $rules);
		return $this;
	}
	
	public function addAllowed($action, $item = null, $rules = null) {
		if (null === $item) {
			$item = $action;
		}
		if ($this->api->isAllowed($action)) {
			$this->add($item, $rules);
		}
		return $this;
	}
	
	public function create($rules) {
		if (is_string($rules)) {
			$args = func_get_args();
			$rules = self::rulesFromArgs($args);
		}
		$menu = $this->menu;
		foreach ($this->items as $item) {
			if (self::match($rules, $item[1])) {
				$menu->add($item[0]);
			}
		}
		return $menu;
	}
	
	public function addRule($item, $rules, $role = null) {
		if (!is_array($rules)) {
			$rules = func_get_args();
			array_shift($rules);
			$role = null;
		}
		if (!isset($this->config[$role])) {
			$this->config[$role] = array();
		}
		if (is_string($rules)) {
			$rules = explode(',', $rules);
		} elseif (!is_array($rules)) {
			$rules = array($rules);
		}
		$this->config[$role][$value] = $rules;
		return $this;
	}
	
	public function addTemplate($name, $options) {
		$this->templates[$name] = $options;
		return $this;
	}
	
}