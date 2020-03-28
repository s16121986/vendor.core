<?php
namespace Html\Page\Head;

class Script extends AbstractMeta{
	
	protected $attributes = [
		'type' => 'text/javascript',
		'async' => false
	];
	
	public function __construct($src, array $attributes = []) {
		$this->setAttributes($attributes);
		$this->src = $src;
	}
	
	public function getHtml() {
		return $this->_getHtml('script', true);
	}
	
}