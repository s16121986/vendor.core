<?php
namespace Http\Content;

class Javascript extends AbstractContent{
	
	protected $fileExtension = 'js';
	
	protected function init() {
		$this->setContentType('application/x-javascript', true);
	}
	
}