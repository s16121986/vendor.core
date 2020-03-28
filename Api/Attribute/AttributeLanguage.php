<?php
namespace Api\Attribute;

use Translation;

class AttributeLanguage extends AbstractAttribute{

	public function checkValue($value) {
		return (bool)Translation::getLanguage($value);
	}

}