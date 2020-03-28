<?php
namespace Api\Util\Settings\Collection;

class Order extends Item{

	protected $params = [
		'name' => null,
		'expression' => null,
		'direction' => \SortDirection::ASC
	];

	public function __construct($name = null, $direction = \SortDirection::ASC) {
		$this->setDirection($direction);
		if ($name) $this->setName($name);
	}

	public function setName($name) {
		if (is_string($name)) {
			if (preg_match('/(.*\W)(asc|desc)\b/si', $name, $matches)) {
				$name = $matches[1];
				$this->setDirection($matches[2]);
			}
			$name = trim($name);
		}
		$this->_set('name', $name);
		return $this;
	}

	public function setDirection($direction) {
		if (\SortDirection::valueExists($direction)) {
			$this->_set('direction', $direction);
		} else {
			if (\SortDirection::keyExists($direction)) {
				$this->_set('direction', \SortDirection::getValue($direction));
			}
		}
		return $this;
	}
	
	public function setExpression($expr) {
		return $this->_set('expression', $expr);
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'orderby':
			case 'name':
				return $this->setName($value);
			case 'direction':
			case 'sortorder':
				return $this->setDirection($value);
		}
		return parent::__set($name, $value);
	}

}