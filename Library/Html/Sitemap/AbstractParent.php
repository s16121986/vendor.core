<?php

namespace Html\Sitemap;

use Html\Sitemap\Tag\Tag;

abstract class AbstractParent extends AbstractXml {

	protected $tag;
	protected $tags = [];

	public function __construct() {
		$this->init();
	}

	abstract protected function init();

	public function __set($name, $value) {
		if (!isset($this->tags[$name]))
			return;

		$this->tags[$name]->setValue($value);
	}
	
	public function addTag($tag) {
		$this->tags[$tag] = new Tag($tag);
		return $this;
	}

	public function __toString() {
		$xml = self::tab(1) . '<' . $this->tag . '>' . self::NL;
		foreach ($this->tags as $tag) {
			if ($tag->isEmpty())
				continue;
			$xml .= self::tab(2) . (string) $tag . self::NL;
		}
		$xml .= self::tab(1) . '</' . $this->tag . '>' . self::NL;
		return $xml;
	}

}
