<?php
namespace Http\Content;

class Image extends File{
	
	protected function initFile() {
		$this->enableCache($this->file->fullname);
		//$this->set('max-age', 29030400);
		switch ($this->file->mime_type) {
			case 'text/html':
			case 'text/plain':
			case 'image/svg':
				$this->setHeaderIf('Content-Type', 'image/svg+xml');
				break;
		}
		$this->setHeader('Content-transfer-encoding', 'binary');
		parent::initFile();
	}
	
}