<?php

namespace Mvc\Controller;

use Exception;

abstract class AbstractActionController extends AbstractController {

	protected $routes = [
		//['/action/path/:var/', ['action' => ''], ['var' => 'regexp']]
		//['/view/:id/', [], ['id' => '\d+']]
	];
	protected $url;
	protected $view;

	protected function init() {
		$this->url = new Url();
		$this->view = new View($this);
		$this->view->setPaths([
			'@layout' => 'layout'
		]);
		parent::init();
	}

	public function getRoutes() {
		return $this->routes;
	}

	public function url($uri = null, $params = null) {
		$url = clone $this->url;
		$url->query = null;
		if ($uri)
			$url->parse($uri);

		return $url->toString();
	}

	public function redirect($url = null, $code = 301) {
		if (is_int($url)) {
			$code = $url;
			$url = null;
		} else {
			$url = $this->url($url);
		}
		switch ($code) {
			case 403:
			case 404:
				throw new Exception('Not found', $code);
		}
		$this->response->setRedirect($url, $code);
	}

	public function layout($path = null, array $attributes = []) {
		$layout = '@layout/layout';
		if (null === $path)
			$path = ['layout' => $layout];
		else if (is_array($path))
			$path['layout'] = $layout;
		else
			$attributes['layout'] = $layout;
		return $this->view->render($path, $attributes);
	}

	public function render($path, array $attributes = []) {
		return $this->view->render($path, $attributes);
	}

	public function onDispatch() {

	}

}