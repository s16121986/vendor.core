<?php
namespace Api\Util;

class Relation{
	
	const TYPE_CASCADE = 'cascade';
	const TYPE_RESTRICT = 'restrict';
	
	private $id;
	private $entity;
	private $type;
	
	public function __construct($entity, $type) {
		$this->entity = $entity;
		$this->type = $type;
		$this->id = $entity->uuid();
	}
	
	public function __get($name) {
		return isset($this->$name) ? $this->$name : null;
	}
	
	public function isRequired() {
		return $this->required;
	}
	
	public function __toString() {
		return (string)$this->entity;
	}
	
}