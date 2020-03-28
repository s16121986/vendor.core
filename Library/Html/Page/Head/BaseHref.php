<?php
namespace Html\Page\Head;

class BaseHref extends AbstractMeta{
	
	public function __construct($href, array $attributes = []) {
		$this->setAttributes($attributes);
		$this->href = $href;
	}
	
	public function getIdentifier() {
		return 'base';
	}
	
	public function getHtml() {
		return $this->_getHtml('base', false);
	}
	
}