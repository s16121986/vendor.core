<?php
namespace Api\Util\TabularSection;

class Row{
	
	private $tabularSection;
	private $data = null;
	
	public function __construct($tabularSection, $data = null) {
		$this->tabularSection = $tabularSection;
		if (null !== $data)
			$this->setData($data);
	}
	
	public function __get($name) {
		if ($name === $this->tabularSection->parentIndex)
			return $this->tabularSection->parentId;
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}
	
	public function setData($data) {
		if (!is_array($data))
			$data = $this->getMainAttributeData($data);
		
		$this->data = null;
		
		$rowData = [];
		$uniqueIndex = $this->tabularSection->getUniqueIndex();
		
		foreach ($this->tabularSection->getAttributes() as $attribute) {
			
			$setFlag = array_key_exists($attribute->name, $data) && $attribute->setValue($data[$attribute->name]);
			
			if ($setFlag) {
				$rowData[$attribute->name] = $attribute->getValue();
			} else {
				if (in_array($attribute->name, $uniqueIndex))
					return false;
				
				$rowData[$attribute->name] = $attribute->default;
			}
		}
		
		$this->data = $rowData;
		
		return true;
	}
	
	public function getData() {
		return array_merge($this->data, [$this->tabularSection->parentIndex => $this->tabularSection->parentId]);
	}
	
	public function getUniqueData() {
		if ($this->isEmpty())
			return [];
		
		$uniqueData = [];
		$uniqueData[$this->tabularSection->parentIndex] = $this->tabularSection->parentId;
		
		foreach ($this->tabularSection->getUniqueIndex() as $k) {
			$uniqueData[$k] = $this->data[$k];
		}
		
		return $uniqueData;
	}
	
	public function isEmpty() {
		return null === $this->data;
	}
	
	public function isValid() {
		if ($this->isEmpty())
			return false;
		
		foreach ($this->tabularSection->getAttributes() as $attribute) {
			if ($attribute->required && !isset($this->data[$attribute->name]))
				return false;
		}
		
		return true;
	}
	
	public function match($data) {
		if (!$this->isEmpty())
			return false;
		
		$matchRow = new self($this->tabularSection, $data);
		if (!$matchRow->isEmpty())
			return false;
		foreach ($this->getUniqueData() as $k => $v) {
			if ($v !== $matchRow->$k)
				return false;
		}
		return true;
	}
	
	private function getMainAttributeData($value) {
		$attributes = $this->tabularSection->getAttributes();
		$attributes = array_values($attributes);
		return [$attributes[1]->name => $value];
	}
	
}