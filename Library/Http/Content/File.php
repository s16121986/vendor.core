<?php
namespace Http\Content;

use File\AbstractFile;

class File extends AbstractContent{
	
	protected $file;
	
	public function __construct(AbstractFile $file) {
		$this->file = $file;
		parent::__construct();
	}
	
	public function setFilename($name) {
		$this->file->name = $name;
	}
	
	protected function init() {
		//$fileModTime = self::outputHeaders($file->fullname);
		//header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 200);
		$maxAge = $this->get('max-age');
		if ($maxAge) {
			$this
				//->setHeader('Pragma', 'public') //deprecated
				->setHeader('Cache-Control', 'public, max-age=' . $maxAge)
				->setHeader('Expires', gmdate('r', time() + $maxAge));
		}
	}
	
	protected function initFile() {
		$file = $this->file;
		$this
			->setHeaderIf('Content-Type', $file->mime_type)
			->setHeader('Content-Disposition', 'inline; filename="' . $file->name . '"')
			->setHeader('Content-Length', $file->getSize())
			->setContent($file->getContents());
	}
	
	public function out() {
		$file = $this->file;
		if (!$file->exists())
			return;
		$this->initFile();
		$this->sendHeaders();
		if (($h = fopen($file->fullname, 'r'))) {
			while (!feof($h)) {
				echo fread($h, 1024);
			}
			fclose($h);
		}
		exit;
	}
	
}