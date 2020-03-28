<?php
namespace Http\Content;

class PlainText extends AbstractContent{
	
	protected function init() {
		$this->setContentType('plain/text', true);
	}
	
}