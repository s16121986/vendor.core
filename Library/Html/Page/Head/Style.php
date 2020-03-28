<?php
namespace Html\Page\Head;

class Style extends AbstractMeta{
	
	protected $attributes = [
		'rel' => 'stylesheet',
		'type' => 'text/css',
		'media' => 'screen'
	];
	
	public function __construct($href, array $attributes = []) {
		$this->setAttributes($attributes);
		$this->href = $href;
	}
	
	public function getHtml() {
		return $this->_getHtml('link', false);
	}
	
}