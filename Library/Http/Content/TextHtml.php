<?php
namespace Http\Content;

class TextHtml extends AbstractContent{
	
	protected function init() {
		$this->setContentType('text/html', true);
	}
	
}