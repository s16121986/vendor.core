<?php

namespace Html\Sitemap;

use Html\Sitemap\Tag\Url;

class Urlset extends AbstractCollection {

	protected $tag = 'urlset';
	protected $tagAttributes = [
		'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
		'xsi:schemaLocation' => 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd',
		'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
		'xmlns:xhtml' => 'http://www.w3.org/1999/xhtml'
	];

	public function addUrl($loc, $lastmod = null, $priority = null, $changefreq = null) {
		if ($loc instanceof Url) {
			$url = $loc;
		} else {
			$url = new Url();

			if (is_array($loc)) {
				foreach ($loc as $k => $v) {
					$url->$k = $v;
				}
			} else {
				$url->loc = $loc;
				$url->lastmod = $lastmod;
				$url->priority = $priority;
				$url->changefreq = $changefreq;
			}
		}

		return $this->add($url);
	}

}
