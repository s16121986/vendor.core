<?php

namespace Html\Sitemap\Tag;

use Html\Sitemap\AbstractParent;

class Sitemap extends AbstractParent {

	protected $tag = 'sitemap';
	
	protected function init() {
		$this
				->addTag('loc')
				->addTag('lastmod');
	}
	
	

}
