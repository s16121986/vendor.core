<?php
namespace Menu;

use Menu\Util\AbstractItem;
use Menu\Util\Item;

class Menu extends AbstractItem{
	
	protected static $optionsAssoc = array(
		'cls' => 'class'
	);
	
	protected $options = array(
		'nodeType' => 'nav'
	);
	protected $items = array();
	protected $relativePath = '/';
	protected $manager = null;
	protected $current = null;
	
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
	
	public function setItems($items) {
		$this->items = array();
		foreach ($items as $item) {
			$this->add($item);
		}
		return $this;
	}
	
	public function getItems() {
		return $this->items;
	}
	
	public function setRelativePath($path) {
		$this->relativePath = $path;
		return $this;
	}
	
	public function setCurrent($current) {
		$this->current = $current;
		return $this;
	}
	
	public function add($label, $uri = null, $action = null, $options = array()) {
		if ($label instanceof Item) {
			$item = clone $label;
		} else {
			if (is_array($label)) {
				$options = $label;
			} else {
				$options['label'] = $label;
				$options['action'] = $action;
				$options['href'] = $uri;
			}
			$item = new Item($options);
		}
		$item->setParent($this);
		$this->items[] = $item;
		return $this;
	}
	
	public function getManager() {
		if (null === $this->manager) {
			$this->manager = new Manager($this);
		}
		return $this->manager;
	}
	
	public function getHtml() {
		if (empty($this->items)) {
			return '';
		}
		$html = '';
		foreach ($this->items as $item) {
			$html .= $this->getItemHtml($item);
		}
		if ($this->nodeType) {
			$html = '<' . $this->nodeType . self::getNodeAttributes($this) . '>' . $html . '</' . $this->nodeType. '>';
		}
		return $html;
	}
	
	public function render($options = array()) {
		$this->setOptions($options);
		return $this->getHtml();
	}
	
	protected function getItemHtml($item) {
		switch (true) {
			case ($item->label === '-'):return ($this->itemNodeType ? '<' . $this->itemNodeType . ' class="hr"></' . $this->itemNodeType . '>' : '<hr />');
		}
		$html = '';
		$class = array();
		//var_dump($this->getRoot()->options, $this->parent);
		if ($item->action && $item->action === $this->getRoot()->current) {
			$class[] = 'current';
		}
		$node = $this->itemNodeType;
		if (!$item->href) {
			$class[] = 'disabled';
			if (!$node) $node = 'div';
		}
		$attr = self::getNodeAttributes($item, implode(' ', $class));
		if ($node) {
			$html .= '<' . $node . $attr . '>';
			$attr = '';
		}
		$text = ($item->icon ? '<i class="fa fa-' . $item->icon . '"></i>' : '')
				. $item->text;
		if ($item->href) {
			$html .= '<a href="' . $this->_url($item->href) . '"' . $attr . '>' . $text . '</a>';
		} else {
			$html .= '<div class="label">' . $text . '</div>';
		}
		if ($item->menu) {
			$html .= $item->menu->getHtml();
		}
		if ($node) {
			$html .= '</' . $node . '>';
		}
		return $html;
	}
	
	protected function _url($url, $path = null) {
		if (0 !== strpos($url, '/') && 0 !== strpos($url, 'http') && 0 !== strpos($url, '#')) {
			$url = $this->relativePath . ($path ? $path . '/' : '') . $url;
		}
		return $url;
	}
	
	protected static function getNodeAttributes($item, $extraCls = '') {
		$class = array();
		if ($extraCls) $class[] = $extraCls;
		if ($item->class) $class[] = $item->class;
		return ($item->id ? ' id="' . $item->id . '"' : '')
			. ($class ? ' class="' . implode(' ', $class) . '"' : '') 
			. ($item->attr ? ' ' . $item->attr : '');
	}
	
}