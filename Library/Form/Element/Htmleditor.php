<?php
namespace Form\Element;

class Htmleditor extends Textarea{

	protected function prepareValue($value) {
		return trim((string)$value);
	}

	public function getHtml() {
        $html = parent::getHtml();
		if (true === $this->htmleditor)
			$html .= '<script type="text/javascript">Application.initHtmlEditor("#' . $this->id . '");</script>';
		return $html;
	}

}
