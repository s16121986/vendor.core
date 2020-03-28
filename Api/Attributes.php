<?php

namespace Api;

use Iterator;
use Api\Attribute\AbstractAttribute;

class Attributes implements Iterator {

	protected $attributes = [];

	public function set($key, AbstractAttribute $val) {
		$this->attributes[$key] = $val;
	}

	public function get($key) {
		return $this->attributes[$key];
	}

	public function current() {
		return current($this->attributes);
	}

	public function key() {
		return key($this->attributes);
	}

	public function next(): void {
		next($this->attributes);
	}

	public function rewind(): void {
		reset($this->attributes);
	}

	public function valid(): bool {
		return null !== key($this->attributes);
	}

}
