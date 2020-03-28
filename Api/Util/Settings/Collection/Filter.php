<?php
namespace Api\Util\Settings\Collection;

class Filter extends Item{
	
	private static $paramsAssoc = [
		'from' => 'valueFrom',
		'to' => 'valueTo',
		'comparison' => 'comparisonType',
		'type' => 'comparisonType'
	];

	protected $params = [
		'name' => null,
		'table' => null,
		'attribute' => null,
		'value' => null,
		'valueFrom' => null,
		'valueTo' => null,
		'comparisonType' => null
	];
	
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

	public function __construct($params) {
		$this->set($params);
	}

	public function __get($name) {
		if (isset(self::$paramsAssoc[$name])) {
			$name = self::$paramsAssoc[$name];
		}
		return (isset($this->params[$name]) ? $this->params[$name] : null);
	}
	
	public function __set($name, $value) {
		if (isset(self::$paramsAssoc[$name])) {
			$name = self::$paramsAssoc[$name];
		}
		parent::__set($name, $value);
	}

	public function isEmpty() {
		if ($this->_attribute) {
			return ($this->_attribute->notnull && null === $this->value && null === $this->valueFrom && null === $this->valueTo);
		}
		return false;
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

	public function setAttribute($attribute) {
		if ($attribute) {
			$this->_set('attribute', $attribute);
			if (!$this->name) {
				$this->name = $attribute->name;
			}
		}
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