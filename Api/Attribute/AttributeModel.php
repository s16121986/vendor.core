<?php
namespace Api\Attribute;

class AttributeModel extends AbstractAttribute{

	private $_model;
	private $joins = [];
	protected $qualifiers = [
		'model' => ''
	];
	
	public function __get($name) {
		switch ($name) {
			case 'joins':return $this->joins;
		}
		return parent::__get($name);
	}

	public function getModel() {
		if (null === $this->_model) {
			$this->_model = \Api::factory($this->model);
		}
		return $this->_model;
	}

	public function getData() {
		return $this->getModel()->getData();
	}

	public function checkValue($value) {
		if (null === $value)
			return !$this->notnull;
		
		return (parent::checkValue($value) && $this->getModel()->findById($value));
	}

	public function prepareValue($value) {
		return null === $value ? $value : (int)$value;
	}

	public function join($columns, $options = null) {
		$this->joins[] = [$columns, $options];
		return $this;
	}
	
	public function joinInner($columns, $options = null) {
		if (!is_array($options)) {
			$options = [];
		}
		$options['type'] = 'inner';
		return $this->join($columns, $options);
	}

}