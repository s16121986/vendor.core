<?php
namespace Page\Head;

abstract class AbstractItem{
	
	protected $options = array();
	
	public function __construct(array $options) {
		foreach ($options as $k => $v) {
			$this->options[$k] = $v;
		}
	}
	
	public function __get($name) {
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}
	
}