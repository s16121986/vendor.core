<?php
namespace Api\Util\Settings;

use Api\Util\Settings\Collection\Order as Item;

class Order extends Collection{
	
	protected $options = [
		'item' => 'Order'
	];
	protected $default = [];

	public function setDirection($direction) {
		foreach ($this->items as $item) {
			$item->setDirection($direction);
		}
		return $this;
	}
	
	public function setDefault($name) {
		$this->default = array();
		$items = func_get_args();
		foreach ($items as $item) {
			$this->default[] = new Item($item);
		}
		return $this;
	}

	public function get($name = null) {
		if (null === $name) {
			return ($this->isEmpty() ? $this->default : $this->items);
		} else {
			if (($item = parent::get($name)))
				return $item;
			foreach ($this->default as $item) {
				if ($item->name === $name)
					return $item;
			}
		}
		return null;
	}

	public function has($name) {
		if (parent::has($name)) {
			return true;
		}
		foreach ($this->default as $item) {
			if ($item->name === $name) {
				return true;
			}
		}
		return false;
	}

	public function reset() {
		$this->default = array();
		return parent::reset();
	}

}