<?php
namespace Api\Util\Settings\Collection;

class Column extends Item{
	
	protected $params = array(
		'expression' => null,
		'name' => null,
		'alias' => null,
		'function' => null,
		'sortable' => true,
		'locale' => false
	);

	public function __construct($name = null, $alias = null) {
		if (is_string($name)) {
			if (null !== $alias) {
				$this->setAlias($alias);
			}
			$this->setName($name);
		} elseif (is_array($name)) {
			foreach ($name as $k => $v) {
				$this->$k = $v;
			}
		}
		if (!$this->alias) {
			$this->setAlias($this->name);
		}
	}

	public function setName($name) {
		if (preg_match('/^' . self::nameRegExp . '$/i', $name)) {
			$this->_set('name', $name);
		} else {
			if (preg_match('/^(' . self::nameRegExp . ') as (' . self::nameRegExp . ')$/si', $name, $matches)) {
				$this->_set('name', $matches[1]);
				$this->setAlias($matches[2]);
			} else {
				$this->setExpression($name);
			}
		}
		return $this;
	}

	public function setAlias($alias) {
		return $this->_set('alias', $alias);
	}
	
	public function setExpression($expression) {
		if (preg_match('/ as (' . self::nameRegExp . ')$/si', $expression, $matches))
			$this->setAlias($matches[1]);
		return $this->_set('expression', $expression);
	}
	
}