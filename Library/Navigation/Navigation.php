<?php

class Navigation{

	protected $options = [
		'nodeType' => 'nav'
	];
	protected $items = [];
	protected $relativePath = '/';
	protected $manager = null;
	protected $current = null;
	
	public function setRelativePath($path) {
		$this->relativePath = $path;
		return $this;
	}
	
	public function setCurrent($current) {
		$this->current = $current;
		return $this;
	}
	
	public function add($label, $url = null, $action = null, $options = []) {
		if ($label instanceof Item) {
			$item = clone $label;
		} else {
			if (is_array($label)) {
				$options = $label;
			} else {
				$options['label'] = $label;
				$options['action'] = $action;
				$options['href'] = $url;
			}
			$item = new Item($options);
		}
		$item->setParent($this);
		$this->items[] = $item;
		return $this;
	}

}
