<?php
namespace Form\Element;

class Month extends Select{

	protected $_options = array(
		'emptyItem' => false,
		'items' => array("gregorian", "stand-alone", "wide")//format
	);

	public function getItems() {
		if (null === $this->_items) {
			$this->_items = array();
			foreach (lang($this->items, 'months') as $i => $l) {
				$this->initItem($i, $l);
			}
		}
		return $this->_items;
	}
}
