<?php

namespace Html\Sitemap;

use Html\Sitemap\Tag\Url;

class Urlset extends AbstractCollection {

	protected $tag = 'urlset';

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
