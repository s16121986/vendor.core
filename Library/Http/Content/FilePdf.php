<?php
namespace Http\Content;

class FilePdf extends File{
	
	protected function initFile() {
		$this->enableCache($this->file->fullname);
		//$this->set('max-age', 29030400);
		$this->setHeader('Content-transfer-encoding', 'binary');
		parent::initFile();
	}
	
}