<?php
namespace Api\Util;

use Api;
use Iterator;
use Countable;
use Exception;

class SelectResult implements Iterator, Countable {
	
	private $items = [];
    private $position = 0;
	
	public function __construct(array $items = []) {
		$this->items = $items;
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
	
	public function isEmpty() {
		return empty($this->items);
	}
	
	public function add($items) {
		if (is_iterable($items)) {
			foreach ($items as $item) {
				if ($this->hasId($item->id))
					continue;
				$this->items[] = $item;
			}
		} else if ($items instanceof Api) {
			if (!$this->hasId($items->id))
				$this->items[] = $items;
		} else
			throw new Exception('Invalid item type');
		
		return $this;
	}
	
	public function filter(array $filter) {
		$newItems = [];
		foreach ($this->items as $item) {
			foreach ($filter as $k => $v) {
				if ($item->$k === $v)
					continue;
				continue 2;
			}
			$newItems[] = $item;
		}
		return new SelectResult($newItems);
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
	
	public function get(array $filter) {
		foreach ($this->items as $item) {
			foreach ($filter as $k => $v) {
				if ($item->$k !== $v)
					continue 2;
			}
			return $item;
		}
		return null;
	}
	
	public function getById($id) {
		return $this->get(['id' => $id]);
	}
	
	public function has(array $filter) {
		return (bool)$this->get($filter);
	}
	
	public function hasId($id) {
		return (bool)$this->get(['id' => $id]);
	}
	
	public function map(callable $callable) {
		return array_map($callable, $this->items);
	}
	
	public function clear() {
		$this->items = [];
		return $this;
	}
	
	public function toArray(/*[attribute names, ...]*/) {
		$args = func_get_args();
		if ($args) {
			return array_map(function($item) use ($args) {
				$a = [];
				foreach ($args as $k) {
					$a[$k] = $item->$k;
				}
				return $a;
			}, $this->items);
		} else {
			return array_map(function($item) {
				return $item->getData();
			}, $this->items);
		}
	}
	
	public function toJSON(/*[attribute names, ...]*/) {
		return json_encode(call_user_func_array([$this, 'toArray'], func_get_args()));
	}
	
}