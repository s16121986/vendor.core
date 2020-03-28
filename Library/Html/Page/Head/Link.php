<?php
namespace Html\Page\Head;

class Link extends AbstractMeta{
	
	public function __construct(array $attributes = []) {
		$this->setAttributes($attributes);
	}
	
	public function getHtml() {
		return $this->_getHtml('link', false);
	}
	
}