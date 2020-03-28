<?php
namespace Mvc\Response;

class Html extends Http{
	
	protected function init() {
		$this->setContentType('text/html', 'default');
		parent::init();
	}
	
}