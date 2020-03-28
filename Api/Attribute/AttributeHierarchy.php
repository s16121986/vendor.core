<?php
namespace Api\Attribute;

class AttributeHierarchy extends AbstractAttribute{

	protected $qualifiers = [
		'notnull' => false
	];

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
		return (parent::checkValue($value) && $this->getModel()->findById($value));
	}

	public function prepareValue($value) {
		return (int)$value;
	}

}