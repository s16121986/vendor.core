<?php

namespace Html\Page;

class JsonLd {

	private $items = [];

	public function __get($name) {
		return isset($this->items[$name]) ? $this->items[$name] : null;
	}

	public function addThing($type, array $data = []) {
		$cls = __NAMESPACE__ . '\JsonLd\\' . $type;
		$item = new $cls($data);
		$this->items[strtolower($type)] = $item;
		return $this;
	}

	public function addOrganization(array $data) {
		return $this->addThing('Organization', $data);
	}

	public function addBreadcrumbs(array $data) {
		return $this->addThing('BreadcrumbList', $data);
	}

	public function getHtml() {
		$html = [];
		foreach ($this->items as $item) {
			$s = $item->getHtml();
			if ($s)
				$html[] = $s;
		}

		return empty($html) ? '' : '<script type="application/ld+json">[' . implode(',', $html) . ']</script>';
	}

}