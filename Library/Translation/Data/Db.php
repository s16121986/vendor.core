<?php
namespace Translation\Data;

use Db as DbObj;
use Exception;
use Api\Exception as ApiException;
use Api\Attribute\Exception as AttributeException;

class Db extends AbstractData{
	
	private $items = null;
	
	public function getItems() {
		if (null === $this->items) {
			$this->items = [];
			$q = DbObj::from('translation_items', ['name', 'value_' . $this->language->code . ' as value'])
				//->where('language=?', $this->language->code)
				//->where('`path` IS NULL' . ($this->path ? ' OR `path`="' . $this->path . '"' : ''))
				//->order('path DESC')
				;
			/*if ($this->path) {
				$q->order('(SELECT path="' . $this->path . '") desc');
			}*/
			$q = $q->query();
			while ($r = $q->fetch()) {
				if (!isset($this->items[$r['name']]))
					$this->items[$r['name']] = $r['value'];
			}
		}
		return $this->items;
	}
	
	public function getContent($value, $path = 'item') {
		$this->getItems();
		switch (true) {
			case $value instanceof AttributeException:
				$value = AttributeException::getErrorKey($value->getCode()) . '_' . $value->attribute;
				$path = 'error';
				break;
			case $value instanceof ApiException:
				$value = ApiException::getErrorKey($value->getCode());
				$path = 'error';
				break;
			case $value instanceof Exception:
				$value = 'error_unknown';
				$path = 'error';
				break;
		}
		list($value, $path) = self::formatValue($value, $path);
		return (isset($this->items[$value]) ? $this->items[$value] : null);
	}
	
}
