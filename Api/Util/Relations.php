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
	
	public function hasRestricted() {
		foreach ($this->items as $relation) {
			if ($relation->ondelete === Relation::ONDELETE_RESTRICT)
				return true;
		}
		return false;
	}
	
	public function restrict($entity, $foreignKey = null) {
		return $this->add($entity, Relation::ONDELETE_RESTRICT, $foreignKey);
	}
	
	public function cascade($entity, $foreignKey = null) {
		return $this->add($entity, Relation::ONDELETE_CASCADE, $foreignKey);
	}
	
	private function add($entity, $ondelete, $foreignKey = null) {
		if (null === $foreignKey)
			$foreignKey = $this->entity->foreignKey;
		
		$api = Api::factory($entity);
		foreach ($api->select([
			$foreignKey => $this->entity->id
		]) as $item) {
			$this->addRelation(new Relation($item, $ondelete));
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