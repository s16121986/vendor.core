<?php

namespace Mvc;

use Mvc\Controller\AbstractController;

class View {

	protected $attributes = [];
	protected $controller;
	protected $paths = [];

	public function __construct(AbstractController $controller) {
		$this->controller = $controller;
	}

	public function __call($name, $arguments) {
		return call_user_func_array([$this->controller, $name], $arguments);
	}

	public function __set($name, $value) {
		$this->attributes[$name] = $value;
	}

	public function __get($name) {
		return $this->controller->get($name);
	}

	public function setPaths(array $paths) {
		$this->paths = $paths;
		return $this;
	}

	public function render($path, array $attributes = []) {
		if (is_array($path)) {
			$router = $this->controller->get('router');
			return $this->render($router->getControllerName() . '/' . $router->getActionName(), $path);
		}

		$attributes = array_merge($this->attributes, $attributes);
		$layout = isset($attributes['layout']) ? $attributes['layout'] : false;
		unset($attributes['layout']);

		extract($attributes);
		ob_start();

		$viewPath = $this->controller->applicationPath . '/View/';
		if (0 === strpos($path, '@')) {
			foreach ($this->paths as $n => $v) {
				if (false === strpos($path, $n))
					continue;
				$path = str_replace($n, '', $path);
				if (0 === strpos($v, '/'))
					$viewPath = $v;
				else
					$viewPath .= $v;
				break;
			}
		}

		include $viewPath . $path . '.phtml';
		$content = ob_get_clean();
		if ($layout) {
			return $this->render($layout, array_merge($attributes, [
				'content' => $content
			]));
		} else {
			return self::prepareContent($content);
		}
	}

	protected static function prepareContent($content) {
		return preg_replace_callback('/{{([a-z0-9_]+):([^}]+)}}/i', function ($matches) {
			return call_user_func($matches[1], $matches[2]);
		}, $content);
	}

}