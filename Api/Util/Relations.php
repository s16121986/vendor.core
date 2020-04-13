<?php
namespace Api\Util;

use Api;
use Iterator;

class Relations implements Iterator{
	
	private $entity;
	private $items = [];
    private $position = 0;
	
	public function __construct($entity) {
		$this->entity = $entity;
	}
	
	public function __get($name) {
		return isset($this->$name) ? $this->$name : null;
	}

    public function rewind() {
        $this->position = 0;
    }

    public function current() {
        return $this->items[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        return isset($this->items[$this->position]);
    }
	
	public function hasType($type) {
		foreach ($this->items as $relation) {
			if ($relation->type === $type)
				return true;
		}
		return false;
	}
	
	public function restrict($entityName, $foreignKey = null) {
		return $this->add(Relation::TYPE_RESTRICT, $entityName, $foreignKey);
	}
	
	public function cascade($entityName, $foreignKey = null) {
		return $this->add(Relation::TYPE_CASCADE, $entityName, $foreignKey);
	}
	
	private function add($type, $entityName, $foreignKey = null) {
		if (null === $foreignKey)
			$foreignKey = $this->entity->foreignKey;
		
		$entity = Api::factory($entityName);
		foreach ($entity->select([
			$foreignKey => $this->entity->id
		]) as $item) {
			$this->addRelation(new Relation($item, $type));
		}
		
		return $this;
	}
	
	private function addRelation($relation) {
		foreach ($this->items as $item) {
			if ($item->id === $relation->id)
				return;
		}
		
		$this->items[] = $relation;
		foreach ($relation->entity->getRelations() as $r) {
			$this->addRelation($r);
		}
	}
	
}