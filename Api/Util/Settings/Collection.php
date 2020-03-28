<?php
namespace Api\Util\Settings;

use Api\Util\Settings\Collection\Item;

class Collection{
	
	protected $options = array(
		'item' => 'Item'
	);
	protected $names = array();
	protected $items = array();
	
	public function __construct($options = array()) {
		if (is_string($options)) {
			$options = array('item' => $options);
		}
		$this->options = array_merge($this->options, $options);
	}
	
	public function __get($name) {
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}
	
	public function add($column, $param = null) {
		$cls = 'Api\Util\Settings\Collection\\' . $this->item;
		if (!($column instanceof $cls)) {
			$column = new $cls($column, $param);
		}
		$this->items[] = $column;
		return $this;
	}

	public function set($columns) {
		$this->reset()->add($columns);
		return $this;
	}
	
	public function getBy($key, $name) {
		foreach ($this->items as $item) {
			if ($item->$key === $name) {
				return $item;
			}
		}
		return null;
	}

	public function get($name = null) {
		if (null === $name) {
			return $this->items;
		} else {
			return $this->getBy('name', $name);
		}
		return null;
	}

	public function has($name) {
		return (bool)$this->getBy('name', $name);
	}
	
	public function remove($name) {
		foreach ($this->items as $i => $item) {
			if ($item->name === $name) {
				array_splice($this->items, $i, 1);
				return true;
			}
		}
		return false;
	}
	
	public function addNames($names) {
		if (is_string($names)) {
			$names = explode(',', $names);
		}
		if (is_array($names)) {
			foreach ($names as $c) {
				if (preg_match('/^' . Item::nameRegExp . '$/i', $c)) {
					$this->names[] = $c;
				}
			}
		}
		return $this;
	}
	
	public function setNames($names) {
		$this->names = array();
		return $this->addNames($names);
	}
	
	public function getNames() {
		return $this->names;
	}
	
	public function hasName($name) {
		return in_array($name, $this->names);
	}

	public function reset() {
		$this->items = array();
		$this->names = array();
		return $this;
	}

	public function isEmpty() {
		return empty($this->items) && empty($this->names);
	}
	
	public function last() {
		if (empty($this->items)){
			return null;
		}
		return $this->items[count($this->items) - 1];
	}

}