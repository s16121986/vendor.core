<?php
namespace Api\Util;

use Api;
use Db;
use Api\Attribute\AttributeFile;
use Api\Util\TabularSection\Data;
use Api\Util\TabularSection\Row as DataRow;

class TabularSection extends BaseApi{
	
	protected $parent;
	protected $parentIndex;
	protected $uniqueIndex = [];
	protected $qualifiers = [];
	protected $data;
	protected $dataDelete;
	protected $clearFlag = false;
	
	public function __construct(Api $parent, $table, $attribute = null, $attributeType = null, $attributeQualifiers = []) {
		$this->parent = $parent;
		$this->parentIndex = $this->parent->getForeignKey();
		$this->table = $table;
		
		$this->data = new Data($this);
		$this->dataDelete = new Data($this);
		
		$this->addAttribute($this->parentIndex, 'model', ['model' => $this->parent->getModelName()]);
		if ($attribute) {
			switch ($attribute) {
				case 'index':
					$this->addIndexAttribute();
					break;
				default:
					$this->addAttribute($attribute, $attributeType, array_merge(['required' => true], $attributeQualifiers));
					$this->uniqueIndex[] = $attribute;
			}
		}
	}
	
	public function __get($name) {
		switch ($name) {
			case 'parentId': return $this->parent->id;
			case 'parentIndex': return $this->parentIndex;
		}
		return parent::__get($name);
	}
	
	protected function initSettings($settings) {
		$settings->filter($this->table . '.' . $this->parentIndex . '=' . $this->parent->id);
		if ($this->getAttribute('index')) {
			$settings->order->setDefault('index');
		}
	}
	
	public function addIndexAttribute() {
		$this->uniqueIndex[] = 'index';
		return $this->addAttribute('index', 'number', []);
	}
	
	public function setUniqueIndex() {
		$this->uniqueIndex = func_get_args();
		return $this;
	}
	
	public function getUniqueIndex() {
		return $this->uniqueIndex;
	}
	
	public function find($data) {
		if ($this->parent->isEmpty())
			return false;
		
		$row = new DataRow($this, $data);
		if ($row->isEmpty())
			return false;
		
		return $this->findRow($row);
	}
	
	public function set($rows) {
		$this->clear();
		
		if (is_string($rows))
			$rows = explode(',', $rows);
		elseif (!is_array($rows))
			$rows = [$rows];
		
		foreach ($rows as $row) {
			$this->add($row);
		}
		
		return $this;
	}
	
	public function add($data) {
		if ($this->clearFlag || !$this->find($data))
			$this->data->add($data);
		$this->dataDelete->remove($data);
		return $this;
	}
	
	public function update($data) {
		$this->dataDelete->remove($data);
		$this->data->update($data);
		return $this;
	}
	
	public function delete($data) {
		$this->data->remove($data);
		$this->dataDelete->add($data);
		return $this;
	}
	
	public function clear() {
		$this->data->clear();
		$this->dataDelete->clear();
		$this->clearFlag = true;
		return $this;
	}
	
	public function get($index) {
		return $this->data->get($index);//(isset($this->data[$index]) ? $this->data[$index] : null);
	}
	
	public function getData() {
		return $this->data->asArray();
	}
	
	public function select($options = null) {
		return ($this->parent->isEmpty() ? $this->data->asArray() : $this->getAdapter()->select($this->getSettings($options)));
	}
	
	public function selectColumn($name, $options = null) {
		if (!is_array($options)) {
			$options = [];
		}
		$options['fields'] = [$name];
		$result = [];
		foreach ($this->select($options) as $r) {
			$result[] = $r[$name];
		}
		return $result;
	}
	
	public function count($data = null) {
		return ($this->parent->isEmpty() ? $this->data->count() : $this->getAdapter()->count($this->getSettings($data)));
	}
	
	public function write() {
		if ($this->parent->isEmpty())
			return false;
		
		if ($this->clearFlag) {
			Db::delete($this->table, [$this->parentIndex => $this->parent->id]);
			$this->clearFlag = false;
		} else {
			foreach ($this->dataDelete->getRows() as $row) {
				Db::delete($this->table, $row->getUniqueData());
			}
		}
		
		$this->dataDelete->clear();

		foreach ($this->data->getRows() as $row) {
			if (!$row->isValid())
				continue;
			
			if ($this->findRow($row)) {
				Db::update($this->table, $row->getData(), $row->getUniqueData());
			} else {
				Db::insert($this->table, $row->getData());
			}
		}
		return true;
	}
	
	public function reset() {
		$this->data->clear();
		$this->dataDelete->clear();
		$this->clearFlag = false;
		return $this;
	}
	
	private function findRow($row) {
		$q = Db::from($this->table);
		
		foreach ($row->getUniqueData() as $k => $v) {
			if ($v === null) {
				$q->where('`' . $k . '` IS NULL');
			} else {
				$q->where('`' . $k . '`=?', $v);
			}
		}
		
		return $q->query()->fetchRow();
	}
	
}