<?php
namespace Grid\View;

class Table extends AbstractView{
	
	protected function _render() {
		$html = '<table class="table-grid">';
		if (false !== $this->grid->header) {
			$html .= $this->renderTHead();
		}
		$html .= $this->renderTBody();
		$html .= $this->renderTFoot();
		$html .= '</table>';
		return $html;
	}

	protected function renderTHead() {
		$data = $this->grid->getData();
		$html = '<thead>';
		$html .= '<tr>';
		foreach ($this->grid->getColumns() as $column) {
			$html .= '<th class="' . $this->_colCls($column) . '">';
			if ($column->order) {
				$html .= '<div class="column-inner">';
				$html .= '<a href="' . $this->orderurl($column) . '">';
				$html .= $column->text;
				$html .= '</a>';
				if ($data->orderby == $column->name) {
					$html .= '<div class="grid-sorted-arrow"></div>';
				}
				$html .= '</div>';
			} else {
				$html .= $column->text;
			}
			$html .= '</th>';
		}
		$html .= '</tr>';
		$html .= '</thead>';
		return $html;
	}

	protected function renderTBody() {
		$html = '<tbody>';
		$ri = 0;
		foreach ($this->grid->getData()->get() as $row) {
			$row = (object)$row;
			$cls = call_user_func_array($this->rowCls, [$row, $ri++]);
			$html .= '<tr' . ($cls ? ' class="' . $cls . '"' : '') . '>';
			$ci = 0;
			foreach ($this->grid->getColumns() as $column) {
				$html .= '<td class="' . $this->_colCls($column) . ' column-' . ($ci++ % 2) . '">';
				$html .= $this->_cell($row, $column);
				$html .= '</td>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';

		return $html;
	}
	
	protected function renderTFoot() {
		$columns = $this->grid->getColumns();
		$html = '';
		foreach ($this->initFeatures() as $feature) {
				$html .= '<tr class="grid-feature-summary">';
				foreach ($columns as $column) {
					$value = $feature->getColumnValue($column);
					if (null === $value) {
						$html .= '<td class="' . $this->_colCls($column) . '">&nbsp;</td>';
					} else {
						$html .= '<td class="' . $this->_colCls($column) . '">' . $column->render($value, array()) . '</td>';
					}
				}
				$html .= '</tr>';
		}
		return ($html ? '<tfoot>' . $html . '</tfoot>' : '');
	}
	
}