<?php
namespace Api\Adapter;

use Db;
use Api\Util\Settings\Collection\Filter as FilterItem;
use Api\Exception;
use Api\Util\Translation;
use Api\Attribute\AttributeDate;
use Api\Attribute\AttributeNumber;
use Api\File as ApiFile;

class Mysql extends AbstractAdapter{
	
	const LOCALE_KEY = '_locale';

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

	protected function quote($key, $value) {
		$this->_query->where('`' . $this->_api->table . '`.`' . $key . '`=?', $value);
	}

	protected function _quote($key) {
        return '`' . $this->_api->table . '`.`' . $key . '`';
    }

	private function prepareValue($attr, $expr, $key, $single = false) {
		if (!isset($expr[$key]))
			return null;
		
		if ($single) {
			if (is_array($expr[$key]))
				return null;
		}

		switch (true) {
			case $attr instanceof AttributeDate:

				if ($single) {
					$val = null;
					$fld = $this->_quote($attr->name);
					switch ($expr[$key]) {
						case 'today':$val = 'DATE_FORMAT(' . $fld . ', "%y-%m-%d")="' . date('y-m-d') . '"';break;
						case 'week':$val = 'YEARWEEK(' . $fld . ')="' . date('YW') . '"';break;
						case 'month':$val = 'DATE_FORMAT(' . $fld . ', "%y-%m")="' . date('y-m') . '"';break;
						case 'all': return null;
					}
					if ($val) {
						return array(
							'condition' => $val,
							'value' => null
						);
					}
				}
				if (
					in_array(
						$expr['comparison'],
						array(
							\ComparisonType::Interval,
							\ComparisonType::IntervalIncludingBounds,
							\ComparisonType::IntervalIncludingLowerBound,
							\ComparisonType::IntervalIncludingUpperBound
						)
					)
						&& $attr->dateFractions == AttributeDate::DateTime
						&& strlen($expr[$key]) == 10
				) {
					switch ($key) {
						case 'valueFrom':$expr[$key] .= ' 00:00:00';break;
						case 'valueTo':$expr[$key] .= ' 23:59:59';break;
					}

				}
			break;
		}

		return $attr->prepareValue($expr[$key]);
	}

	private static function foramtValue($attribute, $value, $comparisonType = null) {
		if (!$attribute) {
			return $value;
		}
		if (null === $value && false === $attribute->notnull) {
			return null;
		}
		if ($attribute->checkValue($value)) {
			if ($comparisonType) {
				switch ($attribute->getType()) {
					case \AttributeType::Date:
						$s = $attribute->toString($value);
						//$dateTime = $attribute->prepareValue($value);
						if ($attribute->dateFractions === 'datetime') {
							switch ($comparisonType) {
								/*case \ComparisonType::Greater:
								case \ComparisonType::GreaterOrEqual:
									$value .= ' 00:00:00';
									break;*/
								case \ComparisonType::Less:
								case \ComparisonType::LessOrEqual:
									$s = str_replace('00:00:00', '23:59:59', $s);
									break;
							}
						}
						return $s;
				}
			}
			return $attribute->prepareValue($value);
		}
		return null;
	}

	protected function filter(FilterItem $item) {
		if (!$item->name) {
			$this->where($item->value);
			return;
		}
		
		if (!isset(self::$_comparisonTypesAssoc[$item->comparisonType]))
			return;
		
		if (($attribute = $item->attribute)) {

		} elseif ($item->name === 'id')
			$attribute = new AttributeNumber('id');
		else
			$attribute = $this->_api->getAttribute($item->name);
		
		if ($attribute->locale)
			$column = '`' . Translation::getTable($this->_api) . '`.`' . $item->name . '`';
		else
			$column = '`' . $item->table . '`.`' . $item->name . '`';
		
		$condition = self::$_comparisonTypesAssoc[$item->comparisonType];
		$value = $item->value;
		if (in_array($item->comparisonType, self::$_simpleComparisonTypes)) {
			$value = self::foramtValue($attribute, $value, $item->comparisonType);
			if (null === $value) {
				switch ($item->comparisonType) {
					case \ComparisonType::Equal:
						$condition = 'IS NULL';
						break;
					case \ComparisonType::NotEqual:
						$condition = 'IS NOT NULL';
						break;
					default :
						$condition = $condition . 'NULL';
				}
			} else {
				$condition .= '?';
			}
		} else {
			switch ($item->comparisonType) {
				case \ComparisonType::InList:
				case \ComparisonType::NotInList:
					if (!is_array($value)) {
						$value = array($value);
					}
					if (empty($value)){
						$condition = null;
					} else {
						$condition = $condition . ' (?)';
					}
					break;
				case \ComparisonType::Contains:
				case \ComparisonType::NotContains:
					$value = self::foramtValue($attribute, $value);
					if (is_string($value) && $value) {
						$value = '%' . $value . '%';
						$condition = $condition . ' (?)';
					} else {
						$condition = null;
					}
					break;
				case \ComparisonType::IntervalIncludingBounds:
				case \ComparisonType::IntervalIncludingLowerBound:
				case \ComparisonType::IntervalIncludingUpperBound:
				case \ComparisonType::Interval:
					$valueFrom = self::foramtValue($attribute, $item->valueFrom, \ComparisonType::GreaterOrEqual);
					$valueTo = self::foramtValue($attribute, $item->valueTo, \ComparisonType::LessOrEqual);
					if (null !== $valueFrom && null !== $valueTo) {
						$this->where($column . ' ' . $condition[0] . '?', $valueFrom);
						$this->where($column . ' ' . $condition[1] . '?', $valueTo);
					}
					$condition = null;
					break;
			}
		}
		if ($condition) {
			$this->where($column . ' ' . $condition, $value);
		}
	}

	public function write(array $data) {
		//if ($this->_api->id === null)
		//	throw new Exception('Empty id error');
		
		/*if (isset($data[self::LOCALE_KEY]) && Translation::getLanguage($data[self::LOCALE_KEY]))
			$languageCode = $data[self::LOCALE_KEY];
		else
			$languageCode = Translation::getCode();*/
		
		unset($data['id']);
		
		$localeData = [];
		
		$primary = [];
				
		foreach ($this->_api->getAttributes() as $attribute) {
			if ($attribute->name === 'id') {
				unset($data[$attribute->name]);
				$primary['id'] = $this->_api->id;
				continue;
			} else if ($attribute->locale) {
				if (!$attribute->changed)
					continue;
				$localeData[$attribute->name] = $attribute->getValue();
				unset($data[$attribute->name]);
			}
			if ($attribute->primary) {
				$primary[$attribute->name] = $attribute->getValue();
				if ($primary[$attribute->name] instanceof ApiFile)
					$primary[$attribute->name] = $primary[$attribute->name]->id;
			}
		}
		
		$isNew = $this->_api->isNew();
		if ($isNew) {
			$result = Db::insert($this->_api->table, $data);
		} else if (empty($primary))
			throw new Exception('Primary data empty');
		else if (empty($data)) 
			$result = $this->_api->id;
		else
			$result = Db::update($this->_api->table, $data, $primary);
		
		if (!$result)
			return false;
		
		if (!$localeData || $result === true)
			return $result;
		
		$id = $result;
		
		$languageCode = Translation::getCode();
		$translationTable = Translation::getTable($this->_api);
		
		$flag = !$isNew && (bool)Db::from($translationTable)
				->where('translatable_id=?', $id)
				->where('language="' . $languageCode . '"')
				->query()->fetchRow();
		
		$where = [
			'translatable_id' => $id,
			'language' => $languageCode
		];

		if ($flag)
			$flag = Db::update($translationTable, $localeData, $where);
		else
			$flag = Db::insert($translationTable, array_merge($localeData, $where));
		
		return $id;
	}

	public function delete($where = null) {
		if ($where === null) {
			$where = [];
			foreach ($this->_api->getAttributes() as $attribute) {
				if ($attribute->name === 'id')
					$where['id'] = $this->_api->id;
				else if ($attribute->primary) {
					$where[$attribute->name] = $attribute->getValue();
					if ($where[$attribute->name] instanceof ApiFile)
						$where[$attribute->name] = $where[$attribute->name]->id;
				}
			}
			if (empty($where))
				throw new Exception('Primary key empty');
		}

		return Db::delete($this->_api->table, $where);
	}

	public function select($settings) {
		$this->_query = Db::from($this->_api->table, null);
		$this->initSelect($settings);
		$result = [];
		$q = $this->_query->query();
		while ($r = $q->fetch())
			$result[] = $this->formatData($r);
		return $result;
	}

	public function count($settings) {
		//$c = '`' .  $this->_api->table . '`.id';
		$this->_query = Db::from($this->_api->table, 'count(*)');
		$this
				->initFilters($settings)
				->initJoins($settings, false)
				->initGroup($settings, true);

		if (method_exists($this->_api, 'beforeCount')) {
			$this->_api->beforeCount($this->_query);
		}
		return (int)$this->_query->query()->fetchColumn(0);
	}

	public function get($settings) {

		$this->_query = \Db::from($this->_api->table, null);
		$this
				->initColumns($settings)
				->initJoins($settings)
				->initFilters($settings)
				->initOrder($settings);
		
		if (method_exists($this->_api, 'beforeData')) {
			$this->_api->beforeData($this->_query);
		}

		return $this->formatData($this->_query->query()->fetchRow());
	}

	public function group($group) {
		$this->_query->group($group);
		return $this;
	}

	public function query($query) {
		return \Db::query($query);
	}

	protected function where($where, $value = null) {
		$this->_query->where($where, $value);
	}

	protected function joinLeft($name, $condition, $columns) {
		$this->_query->joinLeft($name, $condition, $columns);
		return $this;
	}

	protected function joinInner($name, $condition, $columns) {
		$this->_query->joinInner($name, $condition, $columns);
		return $this;
	}

	protected function joinRight($name, $condition, $columns) {
		$this->_query->joinRight($name, $condition, $columns);
		return $this;
	}

	protected function order($order) {
		$this->_query->order($order);
		return $this;
	}

	protected function limit($step, $start) {
		$this->_query->limit($step, $start);
		return $this;
	}

	public function getTableColumns($table) {
		return \Db::getTableFields($table);
	}

	public function fieldToAttributeParams($field) {
		$params = array();
		$fieldType = explode(' ', $field['Type']);
		$fieldTypeAttr = (isset($fieldType[1]) ? $fieldType[1] : null);
		$fieldType = $fieldType[0];
		$fieldTypeParam = 0;
		if (false === ($pos = strpos($fieldType, '('))) {

		} else {
			$fieldTypeParam = substr($fieldType, $pos + 1, -1);
			$fieldType = substr($fieldType, 0, $pos);
		}
		$type = null;
		switch ($fieldType) {
			case 'char':
			case 'varchar':
			case 'text':
			case 'smalltext':
			case 'mediumtext':
			case 'longtext':
				$type = \AttributeType::String;
				$params['length'] = $fieldTypeParam;
				break;
			//case 'longblob':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
				$type = \AttributeType::Number;
				$params['digits'] = $fieldTypeParam;				
				break;
			case 'float':
			case 'decimal':
			case 'double':
				$type = \AttributeType::Number;
				$parts = explode(',', $fieldTypeParam);
				$params['digits'] = $parts[0] - $parts[1];
				$params['fractionDigits'] = $parts[1];
				break;
			case 'timestamp':
			case 'datetime':
				$type = \AttributeType::Date;
				$params['dateFractions'] = AttributeDate::DateTime;
				break;
			case 'date':
				$type = \AttributeType::Date;
				$params['dateFractions'] = AttributeDate::Date;
				break;
			case 'time':
				$type = \AttributeType::Date;
				$params['dateFractions'] = AttributeDate::Time;
				break;
			default:
				return null;
		}
		$params['type'] = $type;
		$params['notnull'] = ('NO' === $field['Null']);
		$params['default'] = $field['Default'];
		if ($type == \AttributeType::Number && (false !== strpos($fieldTypeAttr, 'unsigned'))) {
			$params['nonnegative'] = true;
			$params['digits'] = $params['digits'] * 2;
		}
		if (null === $params['default'] && $params['notnull']) {
			$params['required'] = true;
		}
		return $params;
	}

}