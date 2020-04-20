<?php
namespace Api\Util;

class Relation{
	
	const ONDELETE_CASCADE = 'cascade';
	const ONDELETE_RESTRICT = 'restrict';
	const ONDELETE_SETNULL = 'setnull';
	const ONDELETE_NOACTION = 'noaction';
	
	//const TYPE_MODEL = 'model';
	//const TYPE_TABLE = 'table';
	
	private $id;
	private $entity;
	private $ondelete;
	//private $type;
	
	public function __construct($entity, $ondelete) {
		$this->ondelete = $ondelete;
		$this->entity = $entity;
		$this->id = $entity->uuid();
	}
	
	public function __get($name) {
		return isset($this->$name) ? $this->$name : null;
	}
	
	public function __toString() {
		return (string)$this->entity;
	}
	
}