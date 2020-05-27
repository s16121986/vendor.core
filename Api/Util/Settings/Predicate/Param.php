<?php
namespace Api\Util\Settings\Predicate;

use Stdlib\DateTime;
use Db;

class Param{

	private static $_simpleComparisonTypes = [
		\ComparisonType::Equal,
		\ComparisonType::Greater,
		\ComparisonType::GreaterOrEqual,
		\ComparisonType::Less,
		\ComparisonType::LessOrEqual,
		\ComparisonType::NotEqual,
	];

	private static $_comparisonTypesAssoc = [
		\ComparisonType::Equal => '=',
		\ComparisonType::NotEqual => '<>',
		\ComparisonType::Greater => '>',
		\ComparisonType::GreaterOrEqual => '>=',
		\ComparisonType::Less => '<',
		\ComparisonType::LessOrEqual => '<=',
		\ComparisonType::InList => 'IN',
		\ComparisonType::NotInList => 'NOT IN',
		\ComparisonType::Interval => ['>', '<'],
		\ComparisonType::IntervalIncludingBounds => ['>=', '<='],
		\ComparisonType::IntervalIncludingLowerBound => ['>=', '<'],
		\ComparisonType::IntervalIncludingUpperBound => ['>', '<='],
		\ComparisonType::Contains => 'LIKE',
		\ComparisonType::NotContains => 'NOT LIKE'
	];
	
	private static $paramsAssoc = [
		'from' => 'valueFrom',
		'to' => 'valueTo',
		'comparison' => 'comparisonType',
		'type' => 'comparisonType'
	];

	protected $identifier;
	protected $valueType;
	protected $params = [
		'value' => null,
		'valueFrom' => null,
		'valueTo' => null,
		'comparisonType' => null
	];

	private static function foramtValue($value, $valueType, $comparisonType = null) {
		if (!$valueType)
			return $value;
		
		if (null === $value || !is_scalar($value))
			return null;
		
		switch ($valueType) {
			case 'date':
				return DateTime::serverDate($value);
			case 'datetime':
				if (in_array($comparisonType, [\ComparisonType::Less, \ComparisonType::LessOrEqual]))
					return DateTime::serverDate($value) . ' 23:59:59';
				else
					return DateTime::serverDate($value) . ' 00:00:00';
			case 'float':
				return (float)$value;
			case 'int':
			case 'integer':
				return (int)$value;
			case 'bool':
			case 'boolean':
				return (bool)$value;
		}
		
		return (string)$value;
	}
	
	public static function isScalarValue($value) {
		if (is_array($value)) {
			foreach (['value', 'valueFrom', 'from', 'valueTo', 'to'] as $k) {
				if (array_key_exists($k, $value))
					return false;
			}
		}
		return true;
	}
	
	public static function isNullValue($value) {
		return (null === $value || '' === $value);
	}

	public function __construct($identifier, $value, $valueType = null) {
		$this->set($value);
		$this->identifier = $identifier;
		$this->valueType = $valueType;
	}

	public function __get($name) {
		if (isset(self::$paramsAssoc[$name]))
			$name = self::$paramsAssoc[$name];
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}
	
	public function __set($name, $value) {
		if (isset(self::$paramsAssoc[$name]))
			$name = self::$paramsAssoc[$name];
		$this->_set($name, $value);
	}

	public function isEmpty() {
		return false;
		if ($this->_attribute) {
			return ($this->_attribute->notnull && null === $this->value && null === $this->valueFrom && null === $this->valueTo);
		}
	}

	public function setComparison($value) {
		return $this->setComparisonType($value);
	}

	public function setComparisonType($value) {
		if (\ComparisonType::valueExists($value)) {
			$this->_set('comparisonType', $value);
		} elseif (($type = \ComparisonType::fromString($value))) {
			$this->_set('comparisonType', $type);
		}
		return $this;
	}

	public function setValue($value) {
		$this->_setValue('value', $value);
		return $this;
	}

	public function setValueFrom($value) {
		$this->_setValue('valueFrom', $value);
		return $this;
	}

	public function setValueTo($value) {
		$this->_setValue('valueTo', $value);
		return $this;
	}

	public function set($params) {
		if (self::isScalarValue($params))
			$params = ['value' => $params];
		
		foreach ($params as $k => $v) {
			$this->$k = $v;
		}
		
		if (null === $this->comparisonType) {
			switch (true) {
				case is_array($this->value):
					$this->comparisonType = \ComparisonType::InList;
					break;
				case (!self::isNullValue($this->valueFrom) && !self::isNullValue($this->valueTo)):
					$this->comparisonType = \ComparisonType::IntervalIncludingBounds;
					break;
				case (!self::isNullValue($this->valueFrom)):
					$this->value = $this->valueFrom;
					$this->comparisonType = \ComparisonType::GreaterOrEqual;
					break;
				case (!self::isNullValue($this->valueTo)):
					$this->value = $this->valueTo;
					$this->comparisonType = \ComparisonType::LessOrEqual;
					break;
				default:
					$this->comparisonType = \ComparisonType::Equal;
			}
		}
		return $this;
	}
	
	public function getSqlString() {
		$condition = self::$_comparisonTypesAssoc[$this->comparisonType];
		if (in_array($this->comparisonType, self::$_simpleComparisonTypes)) {
			$value = self::foramtValue($this->value, $this->valueType, $this->comparisonType);
			if (null === $value) {
				switch ($this->comparisonType) {
					case \ComparisonType::Equal:
						$condition = 'IS NULL';
						break;
					case \ComparisonType::NotEqual:
						$condition = 'IS NOT NULL';
						break;
					default :
						$condition = $condition . 'NULL';
				}
				return $this->condition($condition);
			} else {
				return $this->condition($condition . '?', $value);
			}
		} else {
			$value = $this->value;
			switch ($this->comparisonType) {
				case \ComparisonType::InList:
				case \ComparisonType::NotInList:
					if (!is_array($value))
						$value = [$value];
					
					if (empty($value))
						return null;
					
					return $this->condition($condition . ' (?)', $value);
					
				case \ComparisonType::Contains:
				case \ComparisonType::NotContains:
					$value = self::foramtValue($value, 'string');
					if (!is_string($value) || !$value)
						return null;
					
					//$value = '%' . $value . '%';
					return $this->condition($condition . ' (?)', $value);
					
				case \ComparisonType::IntervalIncludingBounds:
				case \ComparisonType::IntervalIncludingLowerBound:
				case \ComparisonType::IntervalIncludingUpperBound:
				case \ComparisonType::Interval:
					$valueFrom = self::foramtValue($this->valueFrom, $this->valueType, \ComparisonType::GreaterOrEqual);
					$valueTo = self::foramtValue($this->valueTo, $this->valueType, \ComparisonType::LessOrEqual);
					if (null === $valueFrom || null === $valueTo)
						return null;
					
					return $this->condition($condition[0] . '?', $valueFrom)
							. ' AND ' . $this->condition($condition[1] . '?', $valueTo);
			}
		}
		
		return null;
	}
	
	protected function condition($condition, $value = null) {
		if (null === $value)
			return $this->identifier . ' ' . $condition;
		
		if (is_array($value)) {
			$a = [];
			foreach ($value as $v) {
				$v = self::foramtValue($v, $this->valueType);
				if (null === $v)
					continue;
				$a[] = $v;
			}
			
			if (empty($a))
				return null;
			
			$sqlValue = implode(',', $a);
		} else
			$sqlValue = Db::quote($value);
		
		return $this->identifier . ' ' . str_replace('?', $sqlValue, $condition);
	}
	
	protected function _set($name, $value) {
		$this->params[$name] = $value;
	}

	protected function _setValue($name, $value) {
		if ($this->_attribute) {
			$attribute = $this->_attribute;
			if (null === $value && $attribute->notnull) {
				return false;
			}
			if ($attribute->checkValue($value)) {
				$value = $attribute->prepareValue($value);
			} else {
				return false;
			}
		}
		$this->_set($name, $value);
		return true;
	}

}
