<?php

namespace Html\Sitemap;

abstract class AbstractXml{
	const NL = "\n";

	protected $tag;
	protected $tagAttributes = [];

	/**
	 * Замена спец. символов xml
	 * @param type $value
	 * @return type
	 */
	protected static function escape($value) {
		if (!$value)
			return '';
		
		return str_replace(['&', '\'', '"', '>', '<'], ['&amp;', '&apos;', '&quot;', '&gt;', '&lt;'], $value);
	}

	protected static function format_date($date) {
		return substr($date, 0, 10);
	}

	/**
	 * Отступы
	 * @param int $n Кол-во отступов
	 * @return type
	 */
	protected static function tab($n = 0) {
		return str_pad('', $n, "\t");
	}
	
}