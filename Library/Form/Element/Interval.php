<?php
namespace Form\Element;

class Interval extends AbstractParent{

	public function getHtml() {
		$html = '<div class="interval-elements">';
		foreach ($this->elements as $element) {
			if ($element->label) {
				$html .= $element->renderLabel();
			}
			$html .= $element->getHtml();
		}
		$html .= '</div>';
		return $html;
	}

}
