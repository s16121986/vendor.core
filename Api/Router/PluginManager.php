<?php

namespace Api\Router;

class PluginManager {

	private $plugins = [];

	public function add($name, $plugin) {
		if (empty($plugin))
			return $this;

		$this->plugins[$name] = $plugin;
		return $this;
	}

	public function get($name) {
		return isset($this->plugins[$name]) ? $this->plugins[$name] : null;
	}

}