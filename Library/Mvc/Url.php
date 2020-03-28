<?php
namespace Mvc;

use Http\Url as AbstractUrl;

class Url extends AbstractUrl{
	
	protected $request;
	protected $homePath = '/';
	
	protected function init() {
		$this->setAlias('home', $this->getHome());
		parent::init();
	}
	
	public function parse($uri) {
		parent::parse($uri);
		$this->path = str_replace($this->homePath, '/', $this->path);
		return $this;
	}
	
	/*public function __set($name, $value) {
		switch ($name) {
			case 'path':
				$isRelative = (null !== $value) && (0 !== strpos($value, self::URI_DELIMITER));
				if ($isRelative) {
					parent::__set('path', null);
					$name = 'relativePath';
				} else {
					parent::__set('relativePath', null);
				}
				$this->initPath();
				break;
			case 'controller':
			case 'action':
			case 'relativePath':
				$this->initPath();
				break;
		}
		return parent::__set($name, $value);
	}*/
	
	public function setHomePath($path) {
		$this->homePath = $path;
		$this->setAlias('home', $path);
		$this->path = str_replace($this->homePath, '/', $this->path);
		return $this;
	}
	
	public function getHome() {
		$uri = parent::getHome();
		return $uri . ltrim($this->homePath, '/');
	}
	
	public function isHome() {
		return !$this->path || $this->path === '/';
	}

}
