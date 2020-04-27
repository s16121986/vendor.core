<?php
namespace Api\Util;

use Iterator;

class SelectResult implements Iterator{
	
	private $items = [];
    private $position = 0;
	
	public function __construct(array $items) {
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
	
	public function isEmpty() {
		return empty($this->items);
	}
	
	public function add(array $items) {
		foreach ($items as $item) {
			if ($this->hasId($item->id))
				continue;
			$this->items[] = $item;
		}
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
	
	public function get(array $filter) {
		foreach ($this->items as $item) {
			foreach ($filter as $k => $v) {
				if ($item->$k === $v)
					continue;
				return $item;
			}
		}
		return null;
	}
	
	public function getById($id) {
		return $this->get(['id' => $id]);
	}
	
	public function has(array $filter) {
		foreach ($this->items as $item) {
			foreach ($filter as $k => $v) {
				if ($item->$k === $v)
					continue;
				return true;
			}
		}
		return (bool)$this->get($filter);
	}
	
	public function hasId($id) {
		return $this->has(['id' => $id]);
	}
	
	public function map(callable $callable) {
		return array_map($callable, $this->items);
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