<?php
namespace Form\Element;

use Db;

class Tree extends Select{

	protected $_options = array(
		'valueInList' => true,
		'allowFolder' => true,
		'valueIndex' => 'id',
		'textIndex' => 'name',
		'parentIndex' => 'parent_id',
		'treeIndent' => '&nbsp;&nbsp;&nbsp;&nbsp;',
		'emptyItem' => false
	);

	protected function getDBItems($data) {
		if (isset($data['table'])) {
			$data = array_merge(array(
				'value' => $this->valueIndex,
				'text' => $this->textIndex,
				'where' => '1',
				'order' => $this->textIndex,
				'parentIndex' => $this->parentIndex
			), $data);
			return Db::from($data['table'], array(
						$data['value'], 
						$data['text'], 
						$data['parentIndex']
					))
					->where($data['where'])
					->order($data['order'])
					->query()->fetchAll();
		}
		return array();
	}
	
	protected function getOptionsHtml() {
		$html = '';
		if (false !== $this->emptyItem) {
			$html .= '<option value="' . self::EMPTY_VALUE . '">' . $this->emptyItem . '</option>';
		}
		$html .= $this->tree();
		return $html;
	}
	
	private function tree($parentId = null, $level = 0) {
		$html = '';
		foreach ($this->getItems() as $item) {
			if ($item->{$this->parentIndex} != $parentId) {
				continue;
			}
			$html .= '<option value="' . self::escape($item->value) . '"' . ($this->isSelected($item->value) ? ' selected' : '') . '>' 
				. self::indentpad($this->treeIndent, $level)
				. $item->text 
				. '</option>';
			$html .= $this->tree($item->value, $level + 1);
		}
		return $html;
	}
	
	private static function indentpad($indent, $count) {
		$str = '';
		for ($i = 0; $i < $count; $i++) {
			$str .= $indent;
		}
		return $str;
	}

}