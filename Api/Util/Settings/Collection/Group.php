<?php
namespace Api\Util\Settings\Collection;

class Group extends Item{
	
	protected $params = array(
		'expression' => null
	);

	public function __construct($expression = null) {
		$this->setExpression($expression);
	}
	
	public function setExpression($expression) {
		return $this->_set('expression', $expression);
	}
	
}