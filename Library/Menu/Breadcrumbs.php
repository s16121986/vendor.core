<?php
namespace Menu;

class Breadcrumbs extends Menu{
	
	protected $options = array(
		'nodeType' => 'nav',
		'class' => 'breadcrumbs',
		'separator' => ' | ',
		'minDepth' => 1,
		'linkLast' => false
	);
	
	public function getHtml() {
		$count = count($this->items);
		if ($this->minDepth < $count) {
			$html = '<' . $this->nodeType. self::getNodeAttributes($this) . '>';
			$count--;
			$menu = array();
			foreach ($this->items as $i => $item) {
				if (false === $this->linkLast && $i == $count) {
					$menu[] = '<div class="disabled">' . $item->text . '</div>';
				} else {
					$menu[] = $this->getItemHtml($item);
				}
			}
			$html .= implode($this->separator, $menu);
			$html .= '</' . $this->nodeType. '>';
			return $html;
		}
		return '';
	}
	
	public function setSeparator($s) {
		$this->separator = $s;
		return $this;
	}
	
	public function setMinDepth($s) {
		$this->minDepth = $s;
		return $this;
	}
	
}