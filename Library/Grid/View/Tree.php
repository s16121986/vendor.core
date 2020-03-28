<?php
namespace Grid\View;

class Tree extends Table{
	
	protected $config = array(
		'parentIndex' => 'parent_id',
		'treeIndent' => '&nbsp;&nbsp;&nbsp;&nbsp;',
		'indentColumn' => 'name'
	);

	protected function renderTBody() {
		$html = '<tbody>';
		$html .= $this->tree(null);
		$html .= '</tbody>';
		return $html;
	}
	
	private function tree($parentId, $level = 0, &$index = 0) {
		$html = '';
		foreach ($this->grid->getData()->get() as $row) {
			if ($row[$this->parentIndex] != $parentId) {
				continue;
			}
			$html .= '<tr' . ($index % 2 ? ' class="alt"' : '') . '>';
			$ci = 0;
			foreach ($this->grid->getColumns() as $column) {
				$html .= '<td class="' . $this->_colCls($column) . ' column-' . ($ci++ % 2) . '">';
				if ($column->name == $this->indentColumn) {
					$html .= self::indentpad($this->treeIndent, $level);
				}
				$html .= $this->_cell($row, $column);
				$html .= '</td>';
			}
			$html .= '</tr>';
			$index++;
			$html .= $this->tree($row['id'], $level + 1, $index);
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