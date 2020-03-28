<?php
namespace Form\Element;

use Translation;

class Language extends Select{

	public function getItems() {
		if (null === $this->_items) {
			$this->_items = array();
			foreach (Translation::getLanguages() as $language) {
				$this->initItem($language->code, $language->name);
			}
		}
		return $this->_items;
	}
}
