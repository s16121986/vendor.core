<?php

namespace Api\Util\Settings;

use Db\Expr;
use Api\Util\Settings\Collection\Item;

class Join extends Item {

	const JOIN_INNER = 'inner';
	//const JOIN_OUTER = 'outer';
	const JOIN_LEFT = 'left';
	const JOIN_RIGHT = 'right';

	protected $columns = null;
	protected $params = [
		'name' => null,
		'alias' => null,
		'condition' => null,
		'type' => self::JOIN_LEFT
	];

	public function __construct($name, $condition, $columns = null, $options = null) {
		$this->columns = new Columns();
		$this->setName($name)
			->setCondition($condition)
			->columns($columns);
		if ($options) {
			if (is_array($options)) {
				foreach ($options as $k => $v) {
					$this->$k = $v;
				}
			} else {
				$this->type = $options;
			}
		}
	}

	public function __set($name, $value) {
		if (method_exists($this, 'set' . $name)) {
			return $this->{'set' . $name}($value);
		}
		if (array_key_exists($name, $this->params)) {
			$this->_set($name, $value);
		}
	}

	public function __get($name) {
		switch ($name) {
			case 'columns':
				return $this->columns;
		}
		if (isset($this->params[$name])) {
			return $this->params[$name];
		}
		return null;
	}

	public function setName($name) {
		if ($name instanceof Expr) {
		} else if (is_array($name)) {
			foreach ($name as $k => $v) {
				$this->setAlias($k);
				$name = $v;
				break;
			}
		} else if (preg_match('/^(.+)\s+as\s+(.+)$/i', $name, $m)) {
			$name = $m[1];
			$this->setAlias($m[2]);
		} else {
			$this->setAlias($name);
		}
		$this->_set('name', $name);
		return $this;
	}

	public function setAlias($alias) {
		$this->_set('alias', $alias);
		return $this;
	}

	public function setCondition($condition) {
		$this->_set('condition', $condition);
		return $this;
	}

	public function columns($columns) {
		if (!is_array($columns)) {
			$columns = [$columns];
		}
		foreach ($columns as $col) {
			$this->addColumn($col);
		}
		return $this;
	}

	public function hasColumn($name) {
		return $this->columns->has($name);
	}

	public function getColumn($name) {
		return $this->columns->get($name);
	}

	public function addColumn($column) {
		$this->columns->add($column);
		return $this;
	}

	public function getTableName() {
		if ($this->name instanceof Expr) {
			return $this->name;
		}
		return $this->name . ' as ' . $this->alias;
	}

	public function getTableExpr() {
		$expr = ($this->name instanceof Expr) ? $this->name : new Expr($this->name);
		return [$this->alias => $expr];
	}

}