<?php

namespace Stdlib;

use Iterator;
use Countable;
use Exception;

class Collection implements Iterator, Countable {

	protected $items = [];
	protected $position = 0;

	public function __construct($items = null) {
		if ($items)
			$this->add($items);
	}

	public function rewind() {
		$this->position = 0;
	}

	public function current() {
		return $this->items[$this->position];
	}

	public function key() {
		return $this->position;
	}

	public function next() {
		++$this->position;
	}

	public function valid() {
		return isset($this->items[$this->position]);
	}

	public function count() {
		return count($this->items);
	}
	
	public function getItems() {
		return $this->items;
	}

	public function isEmpty() {
		return empty($this->items);
	}

	public function notEmpty() {
		return !empty($this->items);
	}

	public function shift() {
		return array_shift($this->items);
	}

	public function first() {
		return empty($this->items) ? null : $this->items[0];
	}

	public function last() {
		return empty($this->items) ? null : $this->items[count($this->items) - 1];
	}

	public function eq($index) {
		return isset($this->items[$index]) ? $this->items[$index] : null;
	}

	public function map(callable $callable) {
		return array_map($callable, $this->items);
	}

	public function clear() {
		$this->items = [];
		return $this;
	}

	public function has($item) {
		if ($this->isItem($item)) {
			$searchId = $this->getItemId($item);
			foreach ($this->items as $item) {
				if ($searchId === $this->getItemId($item))
					return true;
			}
		} else if (is_array($item))
			return (bool) $this->get($item);

		return false;
	}

	public function not($item) {
		if ($this->isItem($item)) {
			$searchId = $this->getItemId($item);
			for ($i = count($this->items) - 1; $i >= 0; $i--) {
				if ($searchId !== $this->getItemId($this->items[$i]))
					continue;
				array_splice($this->items, $i, 1);
				break;
			}
		} else if (is_array($item)) {
			for ($i = count($this->items) - 1; $i >= 0; $i--) {
				if ($this->matchItem($this->items[$i], $item))
					array_splice($this->items, $i, 1);
			}
		} else if ($item instanceof self) {
			foreach ($item as $it) {
				$this->not($it);
			}
		}

		return $this;
	}

	public function filter(array $filter) {
		for ($i = count($this->items) - 1; $i >= 0; $i--) {
			if ($this->matchItem($this->items[$i], $filter))
				continue;
			array_splice($this->items, $i, 1);
		}
		return $this;
	}

	public function get(array $filter) {
		foreach ($this->items as $item) {
			if ($this->matchItem($item, $filter))
				return $item;
		}
		return null;
	}

	public function add($items) {
		if ($this->isItem($items)) {
			if (!$this->has($items))
				$this->items[] = $items;
		} else if (is_iterable($items)) {
			foreach ($items as $item) {
				$this->add($item);
			}
		} else
			throw new Exception('Invalid item type');

		return $this;
	}

	protected function isItem($item) {
		return is_object($item);
	}

	protected function getItemId($item) {
		return spl_object_id($item);
	}

	protected function matchItem($item, array $filter) {
		foreach ($filter as $k => $v) {
			if ($item->$k !== $v)
				return false;
		}
		return true;
	}

}
