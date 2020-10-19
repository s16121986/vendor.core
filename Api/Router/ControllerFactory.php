<?php

namespace Api\Router;

class ControllerFactory {

	private static $controllerPath;
	private static $controllerNamespace;
	private static $instances = [];

	public static function setup($path, $namespace) {
		self::$controllerPath = $path;
		self::$controllerNamespace = $namespace;
		require $path . DIRECTORY_SEPARATOR . 'AbstractController.php';
	}

	public static function factory($name, $path) {
		if (isset(self::$instances[$name]))
			return self::$instances[$name];

		$controllerName = ucfirst($name) . 'Controller';

		include self::$controllerPath . DIRECTORY_SEPARATOR . $controllerName . '.php';

		$controllerClass = self::$controllerNamespace . '\\' . $controllerName;

		self::$instances[$name] = new $controllerClass($path);

		return self::$instances[$name];
	}

	public static function callRoute($name, $method, $path) {
		$controller = self::factory($name, $path);
		return $controller->$method($path);
	}

}