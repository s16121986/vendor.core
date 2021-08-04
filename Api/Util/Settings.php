<?php

namespace Api\Util;

use Translation;
use Api\Util\BaseApi;
use Api\Util\Settings\Collection\Filter as FilterItem;
use Api\Util\Settings\Filter;
use Api\Util\Settings\Columns;
use Api\Util\Settings\Order;
use Api\Util\Settings\Limit;
use Api\Util\Settings\Collection;
use Api\Util\Settings\Quicksearch;
use Api\Util\Settings\Join;
use Api\Util\Settings\Predicate\Param as ParamPredicate;

class Settings {

	protected $_api;
	protected $_columns;
	protected $_order;
	protected $_limit;
	protected $_group;
	protected $_filter;
	protected $_quicksearch;
	protected $_joins = [];
	protected $_params = [];
	protected $predicates = [];

	public function __construct($params, BaseApi $api = null) {
		$this->_api = $api;
		$this->_filter = new Filter();
		$this->_columns = new Columns();
		$this->_order = new Order();
		$this->_limit = new Limit();
		$this->_group = new Collection('Group');
		$this->_quicksearch = new Quicksearch();
		if ($params && is_array($params)) {
			foreach ($params as $k => $v) {
				$this->setParam($k, $v);
			}
		}
	}

	public function init() {
		if ($this->_columns->isEmpty()) {
			$this->columns('*');
		}
		if ($this->_api) {
			foreach ($this->_api->getAttributes() as $attribute) {
				if (!$attribute->filterable || !$this->hasParam($attribute->name))
					continue;

				//$this->predicateIf($attribute->name, $this->_api->table . '.' . $attribute->name, $attribute->getType());
				//continue;

				if ($attribute->filterable) {
					$filter = new FilterItem($this->getParam($attribute->name));
					$filter->setAttribute($attribute);
					$filter->table = $this->_api->table;
					$this->_filter->add($filter);
				}
				if ($attribute->getType() == \AttributeType::Model && $attribute->joins) {
					$joinTable = $attribute->getModel()->table;
					foreach ($attribute->joins as $join) {
						$this->join($joinTable,
							'`' . $this->_api->table . '`.`' . $attribute->name . '`=`' . $joinTable . '`.`id`',
							$join[0],
							$join[1]);
					}
				}
			}
		}
		return $this;
	}

	public function __get($name) {
		if (isset($this->{'_' . $name}))
			return $this->{'_' . $name};

		if (isset($this->$name))
			return $this->$name;

		if (isset($this->_params[$name])) {
			return $this->_params[$name];
		}
		return null;
	}

	public function __set($name, $value) {
		$this->setParam($name, $value);
	}

	public function enableQuickSearch() {
		$columns = func_get_args();
		$this->_quicksearch
			->enable()
			->setColumns($columns);
		return $this;
	}

	public function hasParam($name) {
		return array_key_exists($name, $this->_params);
	}

	public function setParam($name, $value) {
		$this->_params[$name] = $value;
		$this->_setParam($name, $value);
		return $this;
	}

	public function getParam($name) {
		return ($this->hasParam($name) ? $this->_params[$name] : null);
	}

	public function removeParam($name) {
		unset($this->_params[$name]);
		return $this;
	}

	public function getParams() {
		return $this->_params;
	}

	public function columns($columns) {
		$this->_columns->setNames($columns);
		return $this;
	}

	public function order($name, $direction = null) {
		if (!is_array($name))
			$name = [$name];
		foreach ($name as $item) {
			$this->_order->add($item, $direction);
		}
		return $this;
	}

	public function group($group) {
		$this->_group->setNames($group);
		return $this;
	}

	public function join($name, $condition, $columns = null, $options = null) {
		if ($this->_api && is_array($condition)) {
			$condition = '`' . $name . '`.`' . $condition[0] . '`=`' . $this->_api->table . '`.`' . $condition[1] . '`';
		}
		$join = new Join($name, $condition, $columns, $options);
		$this->_joins[$join->alias] = $join;
		return $this;
	}

	public function joinLeft($name, $condition, $columns = null, $options = null) {
		return $this->join($name, $condition, $columns, $options);
	}

	public function joinInner($name, $condition, $columns = null, $options = null) {
		if (!is_array($options)) {
			$options = [];
		}
		$options['type'] = 'inner';
		return $this->join($name, $condition, $columns, $options);
	}

	public function joinTranslation($table, $on, $columns) {
		if (preg_match('/^([a-z_]+) as ([a-z_]+)$/', $table, $m)) {
			$alias = $m[2];
			$translationTable = $m[1] . '_translation as ' . $alias;
		} else {
			$translationTable = $table . '_translation';
			$alias = $translationTable;
		}
		if (preg_match('/^[a-z_]+$/i', $on))
			$on = $this->_api->table . '.' . $on;
		return $this->joinLeft($translationTable,
			$alias . '.translatable_id=' . $on
			. ' AND ' . $alias . '.language="' . Translation::getCode() . '"',
			$columns);
	}

	public function limit($step, $start = 0) {
		$this->_limit->set($step, $start);
		return $this;
	}

	public function filter($filter, $name = null, $table = null) {
		$this->_filter->add($filter, $name, $table);
		return $this;
	}

	public function filterIf($param, $filter, $name = null, $table = null) {
		if ($this->hasParam($param)) {
			$this->filter($filter, $name, $table);
		}
		return $this;
	}

	public function getPredicate($param, $identifier = null, $valueType = null) {
		if (!$this->hasParam($param))
			return false;

		return new ParamPredicate($identifier ?? $param, $this->getParam($param), $valueType);
	}

	public function predicateIf($param, $identifier, $valueType = null) {
		if (!$this->hasParam($param))
			return $this;

		$this->predicates[] = new ParamPredicate($identifier, $this->getParam($param), $valueType);
		return $this;
	}

	public function reset() {
		$this->_filter->reset();
		$this->_columns->reset();
		$this->_group->reset();
		$this->_limit->reset();
		$this->_order->reset();
		$this->_params = [];
		return $this;
	}

	private function _setParam($name, $value) {
		if (empty($value) || $this->_api && $this->_api->getAttribute($name))
			return false;

		switch ($name) {
			case 'fields':
			case 'columns':
				$this->columns($value);
				break;
			case 'order':
			case 'orderby':
				$this->order($value);
				break;
			case 'sortorder':
				if (($item = $this->_order->last())) {
					$item->setDirection($value);
				}
				break;
			case 'limit':
				$this->limit($value);
				break;
			case 'step':
			case 'max-results':
				$this->_limit->setStep($value);
				break;
			case 'start':
			case 'offset':
			case 'start-index':
				$this->_limit->setStart($value);
				break;
			case 'group':
			case 'groupby':
				$this->group($value);
				break;
			case $this->_quicksearch->getParamName():
				$this->_quicksearch->setValue($value);
				break;
			default:
				return false;
		}
		return true;
	}

}