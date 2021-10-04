<?php
namespace Api\Attribute\File\Plugin;

use Api\Attribute\File\Plugin as FilePlugin;

class ImageResize extends FilePlugin{

	private $_sizes = [];

	protected static $_defaultSize = [
		'width' => 0,
		'height' => 0
	];

	public function __construct($options = null) {
		if (isset($options['size'])) {
			$this->addSize($options['size']);
			unset($options['size']);
		}
		if (isset($options['sizes'])) {
			if (is_array($options['sizes'])) {
				foreach ($options['sizes'] as $size) {
					$this->addSize($size);
				}
			}
			unset($options['sizes']);
		}
		parent::__construct($options);
	}

	public function addSize($size) {
		$this->_sizes[] = array_merge(self::$_defaultSize, $size);
		return $this;
	}

	public function init() {
		$file = $this->getFile();
		$formats = [
			'image/jpeg' => 'JPG',
			'image/jpg' => 'JPG',
			'image/png' => 'PNG',
			'image/gif' => 'GIF',
			//'image/x-ms-bmp' => 'XBMP'
		];
		if (!isset($formats[$file->mime_type]))
			return;
		
		$content = $file->getContents();
		foreach ($this->_sizes as $i => $size) {
			$thumb = new \File\Thumb\Gd($content, array(), true);
			$thumb->setFormat($formats[$file->mime_type]);
			$thumb->resize($size['width'], $size['height']);
			if ($i == 0) {
				$file->setContent($thumb->getImageAsString());
			} else {
				$file->addPart($thumb->getImageAsString());
			}
		}
	}

}
