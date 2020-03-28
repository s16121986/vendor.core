<?php
namespace Form\Element;

class Label extends Xhtml{

	protected $_options = [];

	public function isSubmittable() {
		return false;
	}

	public function getHtml() {
		return '<span' . $this->attrToString() . '>' . $this->getValue() . '</span>';
	}

}
