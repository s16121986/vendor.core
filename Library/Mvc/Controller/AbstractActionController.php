<?php

namespace Mvc\Controller;

use Exception;
use Mvc\Application;
use Mvc\Url;
use Mvc\View;

abstract class AbstractActionController extends AbstractController {

	protected $routes = [
		//['/action/path/:var/', ['action' => ''], ['var' => 'regexp']]
		//['/view/:id/', [], ['id' => '\d+']]
	];
	protected $url;
	protected $view;

	public function __construct(Application $application) {
		$this->url = new Url();
		$this->view = new View($this);
		parent::__construct($application);
	}

	public function __get($name) {
		switch ($name) {
			case 'url':
				return $this->$name;
		}

		return $this->get($name);
	}

	protected function init() {
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