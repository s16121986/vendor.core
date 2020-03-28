<?php
namespace Api\Util;

use Translation as AbstractTranslation;
use Api;
use Api\Attribute\AbstractAttribute;

abstract class Translation extends AbstractTranslation{
	
	public static function getColumn($name, $code = null) {
		if ($name instanceof AbstractAttribute) {
			if (!$name->locale) return $name->name;
			$name = $name->name;
		}
		$language = self::getLanguage($code);
		if ($language && !$language->default) {
			return $name . '_' . $language->code;
		}
		return $name;
	}
	
	public static function getTable(Api $api) {
		return $api->table . '_translation';
	}
	
}