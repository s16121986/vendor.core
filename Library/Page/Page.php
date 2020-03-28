<?php
namespace Page;

use Menu\Menu;

require_once 'Enums.php';

class Page{
	
	protected $menu = array();
	protected $head;
	protected $data = array();
	
	public function __construct() {
		$this->head = new Head();
	}
	
	public function __get($name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		if ($this->hasMenu($name)) {
			return $this->getMenu($name);
		}
		return $this->getHead()->getMetaContent($name);
	}
	
	public function __set($name, $value) {
		switch ($name) {
			case 'page_title':
				$this->getHead()->setTitle($value);
				break;
			case 'keywords':
			case 'page_keywords':
				$this->getHead()->addMetaName('keywords', $value);
				break;
			case 'description':
			case 'page_description':
				$this->getHead()->addMetaName('description', $value);
				break;
			case 'options':
				$this->getHead()->setOptions($value);
				break;
			case 'title':
				$this->setTitle($value);
			default:
				$this->data[$name] = $value;
		}
	}
	
	public function setData($data) {
		if (is_array($data)) {
			foreach ($data as $k => $v) {
				$this->$k = $v;
			}
		}
		return $this;
	}
	
	public function getHead() {
		return $this->head;
	}

	public function setTitle($title) {
		$this->getHead()->setTitle($title);
		return $this;
	}
	
	public function doctype() {
		//<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		return '<!DOCTYPE html>';
	}
	
	public function html() {
		return '<html>';
	}

	public function h1() {
		return '<h1>' . ($this->H1 ? $this->H1 : $this->title) . '</h1>';
	}
	
	public function addMenu($name, $menu = array(), $type = 'Menu') {
		if (is_string($menu)) {
			$type = $menu;
			$menu = array();
		}
		if (is_array($menu)) {
			$cls = 'Menu\\' . ucfirst($type);
			$menu = new $cls($menu);
		}
		$menu->page = $this;
		$this->menu[$name] = $menu;
		return $this->menu[$name];
		switch ($name) {
			case 'header':
			case 'main':
			case 'footer':
			case 'page':
			case 'sitemap':
			case 'h1':
			case 'breadcrumbs':
				$cls = 'Api\Model\Page\Menu\\' . ucfirst($name);
				break;
			default:$cls = 'Api\Model\Page\Menu\Menu';
		}
		$this->menu[$name] = new $cls($this, $options);
		return $this->menu[$name];
	}
	
	public function getMenu($name) {
		return ($this->hasMenu($name) ? $this->menu[$name] : null);
	}
	
	public function hasMenu($name) {
		return isset($this->menu[$name]);
	}
	
}