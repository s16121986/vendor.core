<?php
namespace Form\Element;

class Image extends File{

	public function getHtml() {
		$html = '<div id="' . $this->id . '_images" class="box">';
		$value = $this->getValue();
		if ($value) {
			if (!is_array($value)) {
				$value = array($value);
			}
			foreach ($value as $file) {
				if (!$file->guid) {
					continue;
				}
				$html .= '<div class="image-item">';
				$html .='<a href="/file/' . $file . '/" target="_blank"><img src="/file/' . $file . '/" /></a>';
				if ($this->deleteUrl) {
					$html .= '<a href="' . str_replace('%id%', $file->id, $this->deleteUrl) . '" class="button-remove"></a>';
				}				
				if ($this->multiple) {
					$html .= '<input type="hidden" name="' . $this->getOriginalInputName() . '[' . $file->id . '][index][]" value="1" />';
				}
				$html .= '</div>';
			}
			$html .= '<br />';
		}
		$html .= '<input type="file"' . $this->attrToString() . ' />';
		$html .= '</div>';
		if ($this->multiple) {
			$html .= '<script type="text/javascript">$(document).ready(function(){$("#' . $this->id . '_images").sortable({placeholder: "ui-sortable-placeholder"});});</script>';
		}
		return $html;
	}


}
