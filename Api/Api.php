<?php

use Api\Exception;
use Api\Util\TabularSection;
use Api\Attribute\Exception as AttributeException;
use Api\Util\DeleteTransaction;
use Api\Util\Relations;
use Api\Util\SelectResult;
use Api\Attribute\AttributeFile;
use Api\Attribute\AttributeNumber;
use Api\Attribute\AttributePredefined;
use Stdlib\DateTime;

abstract class Api extends Api\Util\BaseApi {

	protected static $modelNamespace = [];
	protected $id = null;
	protected $tabularSections = [];
	protected $record = [];
	protected $foreignKey = null;

	public static function setModelNamespace($namespace) {
		self::$modelNamespace = $namespace;
	}

	public static function factory($name, $id = null) {
		$cls = self::$modelNamespace . $name;
		$api = new $cls();
		if ($id)
			$api->findById($id);

		return $api;
	}

	public function __construct() {
		//$this->initReferences();
		$this->init();
	}

	protected function init() {
		$this->attributes['id'] = new AttributeNumber('id', ['primary' => true]);
	}

	public function __set($name, $value) {

		//$setMethod = 'set' . $name;
		//if (method_exists($this, $setMethod))
		//	return $this->$setMethod($value);

		if (isset($this->tabularSections[$name])) {
			$this->tabularSections[$name]->set($value);
			return true;
		}

		if (!array_key_exists($name, $this->attributes))
			return null;

		if (false === $this->attributes[$name]->setValue($value))
			throw new AttributeException($this->attributes[$name], 'Attribute "' . $name . '" value invalid', Exception::ATTRIBUTE_INVALID);

		return true;
	}

	public function __get($name) {
		switch ($name) {
			case 'id':
			case 'table':
			case 'foreignKey':
				return $this->$name;
		}
		switch (true) {
			case isset($this->attributes[$name]): return $this->attributes[$name]->getValue();
			case isset($this->tabularSections[$name]): return $this->tabularSections[$name];
			case array_key_exists($name, $this->record): return $this->record[$name];
		}

		return parent::__get($name);
	}

	function __isset($name) {
		return $name === 'id' || isset($this->attributes[$name]) || array_key_exists($name, $this->record);
	}

	protected function addTabularSection($name, $table, $attribute = null, $attributeType = null, $attributeQualifiers = []) {
		$this->tabularSections[$name] = new TabularSection($this, $table, $attribute, $attributeType, $attributeQualifiers);
		return $this;
	}

	public function getTabularSection($name) {
		return (isset($this->tabularSections[$name]) ? $this->tabularSections[$name] : null);
	}

	public function getModelName() {
		return str_replace(self::$modelNamespace, '', get_class($this));
	}

	public function isNew() {
		return isset($this->attributes['id']) ? $this->id === 'new' : $this->isEmpty();
	}

	public function isEmpty() {
		if (isset($this->attributes['id']))
			return (null === $this->id || $this->isNew());
		else
			return empty($this->record);
	}

	public function setId($id) {
		if ($id == 'new') {
			$this->reset();
			$this->id = $id;
			return true;
		} elseif ($this->id === $id) {
			return true;
		} elseif ($id) {
			return $this->findById($id);
		}
		return false;
	}

	public function getId() {
		return $this->id;
	}

	public function getData() {
		$data = $this->record;
		foreach ($this->attributes as $attribute) {
			$data[$attribute->name] = $attribute->getValue();
		}
		$data['id'] = $this->id;
		return $data;
	}

	public function setData($data) {
		if (is_array($data)) {
			if (isset($data['id'])) {

				if (!$this->setId($data['id']))
					return $this;

				unset($data['id']);
			}
			foreach ($data as $k => $v) {
				$this->__set($k, $v);
			}
		}
		return $this;
	}

	public function findById($id, $options = null) {
		return $this->findByAttribute('id', (int) $id, $options);
	}

	public function findByAttribute($name, $value, $options = null) {
		if (!is_array($options)) {
			$options = array();
		}
		$options[$name] = $value;
		return $this->findByAttributes($options);
	}

	public function findByAttributes($options) {
		$settings = $this->getSettings($options);
		$record = $this->getAdapter()->get($settings);
		$this->reset();
		if (!$record)
		//throw new \Api\Exception(Exception::);
			return false;
		$this->_setRecord($record);

		return true;
	}

	public function reset() {
		$this->id = null;
		$this->record = [];
		foreach ($this->attributes as $attribute) {
			$attribute->reset();
		}
		foreach ($this->tabularSections as $tabularSection) {
			$tabularSection->reset();
		}
		//$this->getSettings()->reset();
		return $this;
	}

	public function select($data = null) {
		$cls = get_class($this);
		$items = [];
		foreach ($this->getAdapter()->select($this->getSettings($data)) as $row) {
			$api = new $cls();
			$api->_setRecord($row);
			$items[] = $api;
		}
		return new SelectResult($items);
	}

	public function count($data = null) {
		return $this->getAdapter()->count($this->getSettings($data));
	}

	public function write($data = null) {
		if ($data)
			$this->setData($data);

		if (/* false === EventManager::trigger('beforeWrite', $this) || */false === $this->beforeWrite())
			return false;

		if (isset($this->attributes['id']) && null === $this->id)
			throw new Exception(Exception::ID_EMPTY);

		$new = $this->isNew();
		if ($new) {
			foreach ($this->attributes as $k => $attribute) {
				//if ($attribute->type == \Api\Attribute::T_File) continue;
				if (in_array($k, ['created', 'updated', 'id'])) {
					
				} elseif ($attribute instanceof AttributeFile)
					continue;
				elseif ($attribute instanceof AttributePredefined)
					$this->_set($k, $attribute->value);
				else if ($attribute->isEmpty()) {
					if ($attribute->required)
						throw new AttributeException($attribute, 'Attribute "' . $attribute->name . '" value undefined', Exception::ATTRIBUTE_REQUIRED);
					elseif ($attribute instanceof AttributeFile)
						continue;
					else if ($attribute->notnull && null === $attribute->default)
						continue;

					$this->_set($k, $attribute->default);
				}
			}

			if (isset($this->attributes['created']) && $this->attributes['created']->isEmpty())
				$this->_set('created', DateTime::serverDatetime());
		}

		if (false === $this->_write())
			return false;

		//$this->reset();
		$data = [];

		foreach ($this->attributes as $attribute) {
			if ($attribute instanceof AttributeFile) {
				if ($attribute->write()) {
					if ($attribute->fieldName) {
						$data[$attribute->name] = $attribute->getValue()->id;
					}
				}
			}
		}

		if ($data)
			$this->getAdapter()->write($data);

		foreach ($this->tabularSections as $tabularSection) {
			$tabularSection->write();
		}

		$this->afterWrite($new);
		//EventManager::trigger('write', $this);

		return (isset($this->attributes['id']) ? $this->id : true);
	}

	public function data($data = null) {
		return $this->getData();
	}

	public function delete($data = null) {
		if ($this->getAttribute('deletion_mark'))
			return (bool) $this->getAdapter()->write(['deletion_mark' => true]);

		if (!DeleteTransaction::initialized())
			return DeleteTransaction::init($this, false);

		if ($this->isEmpty())
			throw new Exception(Exception::ID_EMPTY);

		/* if (false === EventManager::trigger('beforeDelete', $this))
		  return false; */

		if (false === $this->beforeDelete())
			return false;

		foreach ($this->tabularSections as $tabularSection) {
			$tabularSection->clear()->write();
		}

		if (!$this->getAdapter()->delete())
			throw new Exception(Exception::DELETE_ABORTED);

		$this->afterDelete();
		//EventManager::trigger('delete', $this);
		//$this->reset();
		return true;
	}

	public function deleteForced() {
		return DeleteTransaction::init($this, true);
	}

	public function empty() {
		return new SelectResult();
	}

	public function getRelations() {
		return new Relations($this);
	}

	protected function _set($name, $value) {
		$this->attributes[$name]->setValue($value, true);
		$this->attributes[$name]->setChanged(true);
		return $this;
	}

	protected function _setRecord($record) {
		if (isset($this->attributes['id']))
			$this->id = $record['id'];
		$this->record = $record;

		foreach ($this->attributes as $attribute) {
			if (!isset($record[$attribute->name]))
				continue;
			$attribute->setValue($record[$attribute->name], true);
		}
	}

	protected function _setData($data) {
		foreach ($data as $k => $v) {
			$this->_set($k, $v);
		}
		return $this;
	}

	protected function _update() {
		if (!$this->isEmpty() && isset($this->attributes['updated']))
			$this->getAdapter()->query('UPDATE `' . $this->table . '` SET updated=CURRENT_TIMESTAMP WHERE id=' . $this->id());
		return $this;
	}

	protected function _write() {
		if (isset($this->attributes['updated']) && !$this->attributes['updated']->changed)
			$this->_set('updated', DateTime::serverDatetime());
		if ($this->isNew() && isset($this->attributes['created']))
			$this->_set('created', DateTime::serverDatetime());

		$data = [];
		foreach ($this->attributes as $attribute) {
			if ($attribute instanceof AttributeFile || !$attribute->changed)
				continue;
			$data[$attribute->name] = $attribute->getValue();
		}

		if (empty($data)) {
			if ($this->isNew())
				throw new Exception('Data empty');
		} else {
			$newId = $this->getAdapter()->write($data);
			if (!$newId)
				throw new Exception('Cant write api');
		}

		if ($this->isNew())
			$this->id = $newId;

		foreach ($this->attributes as $attribute) {
			$attribute->setChanged(false);
		}

		return $this->id;
	}

	protected function beforeWrite() {
		
	}

	protected function afterWrite($isNew = false) {
		
	}

	protected function beforeDelete() {
		
	}

	protected function afterDelete() {
		
	}

	public function uuid() {
		$uuid = strtolower($this->getModelName());

		$pk = [];
		foreach ($this->attributes as $attribute) {
			if (!$attribute->primary)
				continue;

			$pk[] = (string) $attribute->getValue();
		}

		return $uuid . '_' . implode('-', $pk);
	}

}
