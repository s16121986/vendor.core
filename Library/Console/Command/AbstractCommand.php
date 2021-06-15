<?php
namespace Console\Command;

use Console\Handler as ConsoleHandler;
use Console\Terminal;
use Exception;

abstract class AbstractCommand{
	
	protected $debug = false;
	protected $tokens = [];
	
	public function __construct(array $tokens) {
		$this->tokens = $tokens;
		$this->debug = $this->hasFlag('debug', 'd');
		$this->init();
	}

	public function isCallable($action) {
		return method_exists($this, $action);
	}
	
	public function getAttribute($name, $shortName = null, $default = null) {
		if (null === $shortName)
			$shortName = $name;
		
		foreach ($this->tokens as $i => $token) {
			if ($token !== '-' . $shortName && $token !== '--' . $name)
				continue;
			
			if (!isset($this->tokens[$i + 1]))
				throw new Exception('Attribute "' . $name . '" undefined');
			
			return $this->tokens[$i + 1];
		}
		
		return $default;
	}
	
	public function hasAttribute($name, $shortName = null) {
		if (null === $shortName)
			$shortName = $name;
		
		foreach ($this->tokens as $i => $token) {
			if ($token === '-' . $shortName || $token === '--' . $name)
				return isset($this->tokens[$i + 1]);
		}
		return false;
	}
	
	public function hasFlag($name, $shortName = null) {
		$flagName = '--' . $name;
		$flagShortName = '-' . $shortName;
		foreach ($this->tokens as $token) {
			if ($token === $flagName || ($shortName && $token === $flagShortName))
				return true;
		}
		return false;
	}
	
	public function getArgument($index = 0) {
		$i = 0;
		foreach ($this->tokens as $token) {
			if (0 === strpos($token, '-'))
				continue;
			if ($i === $index)
				return $token;
			$i++;
		}
		return null;
	}
	
	abstract public function main();
	
	protected function init() {}
	
	protected function console($command, array $tokens = null) {
		if (null === $tokens)
			$tokens = $this->tokens;
		$this->debug('run ' . $command);
		ConsoleHandler::command($command, $tokens);
	}
	
	protected function out($result, $color = null, $background = null) {
		Terminal::out($result, $color, $background);
	}
	
	protected function debug($result) {
		if ($this->debug)
			$this->out($result);
	}
	
}