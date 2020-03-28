<?php
namespace Stdlib;

abstract class AbstractCollection{

	protected $items = array();

	public function count() {
		return count($this->items);
	}
	
	public function get($name) {
		if (null === $name) {
			return $this->items;
		}
		foreach ($this->items as $item) {
			if ($item->name === $name) {
				return $item;
			}
		}
		return null;
	}
	
	public function clear() {
		$this->items = array();
		return $this;
	}

	public function reset() {
		return $this->clear();
	}
	
	public function delete($name) {
		foreach ($this->items as $i => $item) {
			if ($item->name === $name) {
				array_splice($this->items, $i, 1);
				return true;
			}
		}
		return false;
	}
	
	public function remove($name) {
		return $this->delete($name);
	}

	public function has($name) {
		foreach ($this->items as $item) {
			if ($item->name === $name) {
				return true;
			}
		}
		return false;
	}

	public function isEmpty() {
		return empty($this->items);
	}

	protected function _add($item) {
		$this->items[] = $item;
		return $item;
	}

}