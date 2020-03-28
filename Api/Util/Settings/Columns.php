<?php
namespace Api\Util\Settings;

class Columns extends Collection{
	
	protected $options = array(
		'item' => 'Column'
	);
	private $all = true;
	
	public function setNames($names) {
		$this->all = false;
		$this->names = array();
		if ('*' === $names) {
			$this->all = true;
		} else {
			return parent::setNames($names);
		}
		return $this;
	}
	
	public function hasName($name) {
		return ($this->all) || parent::hasName($name);
	}

	public function has($name) {
		return (bool)$this->getBy('alias', $name);
	}

	public function get($name = null) {
		if (null === $name) {
			return $this->items;
		} else {
			return $this->getBy('alias', $name);
		}
		return null;
	}

	public function reset() {
		$this->all = false;
		return parent::reset();
	}

}