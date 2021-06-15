<?php

namespace Console;

use Autoload;
use Exception;

class Handler {

	private static $options = [
		'commandsNamespace' => 'Console\Command\\'
	];

	public static function setup(array $options = []) {
		self::$options = array_merge(self::$options, $options);

		require_once 'Command/AbstractCommand.php';

		Autoload::addPath(self::getOption('commandsPath'), self::getOption('commandsNamespace'), '');
	}

	public static function run(array $argv = null) {
		if (null === $argv)
			$argv = $_SERVER['argv'];

		// strip the application name
		array_shift($argv);

		$tokens = $argv;

		$command = array_shift($tokens);

		try {
			self::command($command, $tokens);
		} catch (Exception $ex) {
			self::out($ex->getMessage(), 'error');
			self::out($ex->getTraceAsString(), 'error');
			exit(0);
		}

		if (self::hasOption('logFile')) {
			$filename = self::getOption('logFile');
			$h = fopen($filename, 'a+');
			fwrite($h, now()->format('Y-m-d H:i:s') . ' bin/console ' . implode(' ', $argv) . "\n");
			fclose($h);
		}
	}

	public static function toCamelCase($name) {
		$array = [];
		$args = explode('-', $name);
		$array[] = array_shift($args);
		foreach ($args as $arg) {
			$array[] = ucfirst($arg);
		}
		return implode('', $array);
	}

	public static function command($token, $tokens) {
		//action name
		$args = explode(':', $token);
		if (count($args) === 1)
			$action = 'main';
		else
			$action = self::toCamelCase(array_pop($args));
		//called class

		$commandClass = self::getCommandClass($args);
		$api = new $commandClass($tokens);

		if (!$api->isCallable($action))
			throw new Exception('action not exists');

		$result = $api->$action();
	}

	public static function out($result, $color = null, $background = null) {
		Terminal::out($result, $color, $background);
	}

	private static function hasOption($name) {
		return isset(self::$options[$name]);
	}

	private static function getOption($name) {
		return isset(self::$options[$name]) ? self::$options[$name] : null;
	}

	private static function getCommandClass($args) {
		$cls = array_map(function ($arg) {
			return ucfirst($arg);
		}, $args);

		$commandClass = self::getOption('commandsNamespace') . implode('\\', $cls);
		if (!class_exists($commandClass, true))
			throw new Exception('command "' . $commandClass . '" not exists');

		/*if (class_exists($commandClass, false))
			return $commandClass;

		$commandFile = self::getOption('commandsPath') . DIRECTORY_SEPARATOR
			. implode(DIRECTORY_SEPARATOR, $cls)
			. '.php';
		if (!file_exists($commandFile))
			throw new Exception('command file "' . $commandFile . '" not exists');

		include_once $commandFile;

		if (!class_exists($commandClass, false))
			throw new Exception('command "' . $commandClass . '" not exists');*/

		return $commandClass;
	}

}
