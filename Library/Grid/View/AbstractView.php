<?php
namespace Grid\View;

use Grid;

abstract class AbstractView{
	
	protected $grid;
	protected $config = [];
	protected $_url = null;
	
	public function __construct(Grid $grid) {
		$this->grid = $grid;
		if ($grid->viewConfig) {
			$this->config = array_merge($this->config, $grid->viewConfig);
		}
	}
	
	public function __get($name) {
		if (isset($this->config[$name])) {
			return $this->config[$name];
		}
		return $this->grid->$name;
	}
	
	public function render() {
		if ($this->grid->isEmpty()) {
			return '<div class="grid-empty-text">' . $this->grid->emptyGridText . '</div>';
		}
		return $this->_render();
	}
	
	public function initFeatures() {
		if (!$this->grid->features) {
			return array();
		}
		$features = array();
		$featuresTemp = $this->grid->features;
		if (!is_array($featuresTemp)) {
			$featuresTemp = array($featuresTemp);
		}
		foreach ($featuresTemp as $name) {
			if (!preg_match('/^[a-z_]+$/i', $name)) {
				throw new \Exception('Invalid feature');
			}
			$cls = 'Grid\Feature\\' . ucfirst($name);
			if (!class_exists($cls, true)) {
				throw new \Exception('Feature not exists');
			}
			$features[$name] = new $cls($this->grid);
		}
		return $features;
	}
	
	protected function orderurl($column) {
		if (null === $this->_url) {
			if ($this->grid->orderUrl) {
				$this->_url = $this->grid->orderUrl;
			} else {
				$url = $_SERVER['REQUEST_URI'];
				if (false !== ($pos = strpos($url, '?'))) {
					$url = substr($url, 0, $pos);
				}
				$this->_url = $url;
			}
		}
		$dir = 'asc';
		if ($this->data->orderby == $column->name) {
			$dir = ($this->data->sortorder == 'asc' ? 'desc' : 'asc');
		}
		$q = $_GET;
		if ($this->grid->orderparams) {
			$q = array_merge($q, $this->grid->orderparams);
		}
		$q['orderby'] = $column->name;
		$q['sortorder'] = $dir;
		return $this->_url . '?' . http_build_query($q);
	}

	protected function _cell($row, $column) {
		return $column->render(isset($row->{$column->name}) ? $row->{$column->name} : null, $row);
	}

	protected function _colCls($column) {
		$data = $this->grid->getData();
		return 'column-' . $column->type . ' column-' . $column->name
			. ($column->class ? ' ' . $column->class : '')
			. ($data->orderby == $column->name ? ' column-sorted column-sorted-' . $data->sortorder : '');
	}
	
	protected static function rowCls($row, $index) {
		return ($index % 2 ? 'alt' : '');
	}
	
	abstract protected function _render();
	
}