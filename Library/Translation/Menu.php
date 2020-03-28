<?php
namespace Translation;

use Translation;
use Menu\Menu as BaseMenu;

class Menu extends BaseMenu{
	public function __construct($options = array()) {
		$langCode = Translation::getCode();
		$this->setCurrent($langCode);
		$uri = $_SERVER['REQUEST_URI'];
		if (0 === strpos($uri, '/' . $langCode . '/')) {
			$uri = substr($uri, 3);
		}
		foreach (Translation::getLanguages() as $lang) {
			$this->add(array(
				'text' => $lang->name,
				'href' => ($langCode === $lang->code ? null : $lang->code . $uri),
				'action' => $lang->code,
				'class' => $lang->code
			));
		}
		parent::__construct(array_merge(array('class' => 'language'), $options));
	}
}