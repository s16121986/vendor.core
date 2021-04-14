<?php

namespace Html\Sitemap\Tag;

use Html\Sitemap\AbstractParent;

class Url extends AbstractParent {

	protected $tag = 'url';
	
	protected function init() {
		$this
				->addTag('loc')
				->addTag('lastmod')
				->addTag('changefreq')
				->addTag('priority');
	}
	
	public function addAlternate($hreflang, $href) {
		$this->tags[] = new Tag('xhtml:link', [
			'rel' => 'alternate',
			'hreflang' => $hreflang,
			'href' => $href
		]);
	}

}
