<?php

namespace Html\Sitemap;

use Html\Sitemap\Tag\Sitemap;

class Sitemapindex extends AbstractCollection {

	protected $tag = 'sitemapindex';
	protected $tagAttributes = ['xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9'];

	public function addSitemap($loc, $lastmod = null) {
		$sitemap = new Sitemap();

		if (is_array($loc)) {
			foreach ($loc as $k => $v) {
				$sitemap->$k = $v;
			}
		} else {
			$sitemap->loc = $loc;
			$sitemap->lastmod = $lastmod;
		}

		return $this->add($sitemap);
	}

}
