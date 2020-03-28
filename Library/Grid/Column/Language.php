<?php
namespace Grid\Column;

use Translation;

class Language extends AbstractColumn{

	public function formatValue($value, $row = null) {
		return ($language = Translation::getLanguage($value)) ? $language->name : '';
	}

}