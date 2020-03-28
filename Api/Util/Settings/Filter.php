<?php
namespace Api\Util\Settings;

use Api\Util\Settings\Collection\Filter as Item;
use Api\Attribute\AbstractAttribute;

class Filter extends Collection{
	
	protected $options = array(
		'item' => 'Filter'
	);

	public function add($value, $name = null, $table = null) {
		if ($value instanceof Item) {
			$filter = $value;
		} else {
			if (Item::isScalarValue($value)) {
				$filter = array('value' => $value);
			} else {
				$filter = $value;
			}
			if ($name instanceof AbstractAttribute) {
				$filter['attribute'] = $name;
			} elseif ($name) {
				$filter['name'] = $name;
			}
			if ($table) {
				$filter['table'] = $table;
			}
			$filter = new Item($filter);
		}
		return parent::add($filter);
	}

}