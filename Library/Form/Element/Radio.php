<?php
namespace Form\Element;

class Radio extends Xhtml{

	protected $_items = null;

	protected function initItem($value, $text = '') {
		if (is_array($text)) {
			$value = $text;
			$text = null;
		}
		$this->_items[] = new Select\Item($value, $text);
	}

	protected static function getDBItems($data) {
		if (isset($data['table'])) {
			$data = array_merge(array(
				'value' => 'id',
				'text' => 'name',
				'where' => '1',
				'order' => 'name'
			), $data);
			$fields = array($data['value'] . ' as value', $data['text'] . ' as text');
			$order = array($data['order']);
			return \Db::from($data['table'], $fields)
					->where($data['where'])
					->order($order)
					->query()->fetchAll();
		}
		return array();
	}
	
	private static function getValueId($value) {
		if (is_string($value) && preg_match('/\d+/', $value)) {
			$value = (int)$value;
		}
		return $value;
	}

	public function isEmpty() {
		return (null === $this->_value);
	}

	public function getItems() {
		if (null === $this->_items) {
			$this->_items = array();
			$itemsTemp = array();
			$itemsData = $this->items;
			if (is_array($itemsData)) {
				$itemsTemp = self::getDBItems($itemsData);
				unset($itemsData['where'], $itemsData['order'], $itemsData['text'], $itemsData['value'], $itemsData['table']);
			}
			if (is_array($itemsData)) {
				foreach ($itemsData as $k => $v) {
					$this->initItem($k, $v);
				}
			}
			foreach ($itemsTemp as $v) {
				$this->initItem($v);
			}
		}
		return $this->_items;
	}

	public function checkValue($value) {
		return true;
	}

	protected function prepareValue($value) {
		return self::getValueId($value);
	}

	public function getValuePresentation() {
		foreach ($this->getItems() as $item) {
			if ($item->value == $this->_value) {
				return $item->getPresentation();
			}
		}
		return '';
	}
	
	public function isSelected($value) {
		$value = self::getValueId($value);
		return ($this->value === $value);
	}

	public function getHtml() {
		$html = '<div class="field-radio-box">';//'<select' . $this->attrToString() . '>';
		$i = 0;
		foreach ($this->getItems() as $item) {
			$isSel = $this->isSelected($item->value);
			$html .= '<div class="radio-item">'
				. '<input type="radio" id="' . $this->id . '_' . $i . '" name="' . $this->getInputName() . '" value="' . self::escape($item->value) . '"' . ($isSel ? ' checked' : '') . '>'
				. '<label for="' . $this->id . '_' . $i . '">' . $item->text . '</label>'
				. '</div>';
			$i++;
		}
		$html .= '</div>';
		return $html;
	}
}
