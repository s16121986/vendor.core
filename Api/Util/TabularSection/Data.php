<?php
namespace Api\Util\TabularSection;

class Data{
	
	private $tabularSection;
	private $rows = [];
	
	public function __construct($tabularSection) {
		$this->tabularSection = $tabularSection;
	}
	
	public function setUniqueIndex() {
		$this->uniqueIndex = func_get_args();
		return $this;
	}
	
	public function add($data) {
		if ($this->has($data))
			return;
		
		$row = $this->getRow($data);
		if ($row)
			$this->rows[] = $row;
	}
	
	public function update($data) {
		
		foreach ($this->rows as $i => $row) {
			if (!$row->match($data))
				continue;
			
			$newRow = $this->getRow($data);
			if ($newRow)
				$this->rows[$i] = $newRow;
				
			return;
		}
		
		$row = $this->getRow($data);
		if ($row)
			$this->rows[] = $row;
	}
	
	public function remove($data) {
		foreach ($this->rows as $i => $row) {
			if (!$row->match($data))
				continue;
			array_splice($this->rows, $i, 1);
			break;
		}
	}
	
	public function clear() {
		$this->rows = [];
	}
	
	public function has($data) {
		foreach ($this->rows as $row) {
			if ($row->match($data))
				return true;
		}
		return false;
	}
	
	public function getRows() {
		return $this->rows;
	}
	
	public function asArray() {
		$array = [];
		foreach ($this->rows as $row) {
			$array[] = $row->getData();
		}
		return $array;
	}
	
	private function getRow($data) {
		$row = new Row($this->tabularSection, $data);
		if ($row->isEmpty())
			return false;
		return $row;
	}
	
}