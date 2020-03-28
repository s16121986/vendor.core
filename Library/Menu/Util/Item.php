<?php
namespace Menu\Util;

use Menu\Menu;

class Item extends AbstractItem{
	
	protected static $optionsAssoc = array(
		'url' => 'href',
		'uri' => 'href',
		'cls' => 'class',
		'caption' => 'text',
		'label' => 'text'
	);
	
	protected $options = array(
		'text' => '',
		'href' => '',
		'hrefTarget' => '',
		'icon' => '',
		'id' => '',
		'class' => ''
	);
	protected $menu = null;
	
	public function __set($name, $value) {
		if (isset(self::$optionsAssoc[$name])) {
			$name = self::$optionsAssoc[$name];
		}
		return parent::__set($name, $value);
	}
	
	public function __get($name) {
		if (isset(self::$optionsAssoc[$name])) {
			$name = self::$optionsAssoc[$name];
		}
		return parent::__get($name);
	}
	
	public function setMenu($menu) {
		if(!($menu instanceof Menu)) {
			if (!isset($menu['items'])) {
				$menu = array('items' => $menu);
			}
			$menu = new Menu($menu);
		}
		$this->menu = $menu;
		$menu->setParent($this);
		return $this;
	}
	
	public function setItems($items) {
		if (null === $this->menu) {
			$this->setMenu(array());
		}
		$this->menu->setItems($items);
		return $this;
	}
	
	public function getMenu() {
		return $this->menu;
	}
	
}