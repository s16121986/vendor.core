<?php
namespace Http\Content;

class TextCss extends AbstractContent{
	
	protected $fileExtension = 'css';
	
	protected function init() {
		$this->setContentType('text/css', true);
	}
	
}