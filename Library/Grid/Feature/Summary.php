<?php
namespace Grid\Feature;

class Summary extends AbstractFeature{
	
	//count, sum, min, max, avarege
	
	public function getColumnValue($column) {
		$values = array();
		foreach ($this->grid->getData()->get() as $row) {
			if (array_key_exists($column->name, $row)) {
				$values[] = $column->prepareValue($row[$column->name]);
			}
		}
		switch ($column->summaryType) {
			case 'sum':return array_sum($values);
			case 'count':return count($values);
			case 'min':return min($values);
			case 'max':return max($values);
			case 'avarege':return array_sum($values) / count($values);
		}
		return null;
	}
	
	public function render() {
		$html = '<tr class="grid-feature-summary">';
		foreach ($this->grid->getColumns() as $column) {
			$html .= '<td class="">';
				$html .= $column->text;
			$html .= '</td>';
		}
		$html .= '</tr>';
		return $html;
	}
	
}