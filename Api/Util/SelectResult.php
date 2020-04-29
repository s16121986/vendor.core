<?php

namespace Api\Util;

use Api;
use Stdlib\Collection;

class SelectResult extends Collection {

	public function getById($id) {
		return $this->get(['id' => $id]);
	}

	public function hasId($id) {
		return (bool) $this->get(['id' => $id]);
	}

	public function toArray(/* [attribute names, ...] */) {
		$args = func_get_args();
		if ($args) {
			return array_map(function($item) use ($args) {
				$a = [];
				foreach ($args as $k) {
					$a[$k] = $item->$k;
				}
				return $a;
			}, $this->items);
		} else {
			return array_map(function($item) {
				return $item->getData();
			}, $this->items);
		}
	}

	public function toJSON(/* [attribute names, ...] */) {
		return json_encode(call_user_func_array([$this, 'toArray'], func_get_args()));
	}
	
	protected function isItem($item) {
		return $item instanceof Api;
	}
	
	protected function getItemId($item) {
		return $item->id;
	}

}
