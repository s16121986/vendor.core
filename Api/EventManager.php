<?php
namespace Api;

use Api\EventManager\Event;

abstract class EventManager{
	
	private static $triggers = array();
	
	public static function bind($action, $callback, $api = null, $params = null) {
		self::$triggers[] = array($action, self::getCallback($callback, $api), $api, $params);
	}
	
	public static function unbind($action, $callback = null, $api = null) {
		$triggers = array();
		$callback = self::getCallback($callback, $api);
		foreach (self::$triggers as $item) {
			if ($item[0] === $action && self::isEqual($callback, $item[1], true) && self::isEqual($api, $item[2], true)) {
				$triggers[] = $item;
			}
		}
		self::$triggers = $triggers;
	}
	
	public static function trigger($action, $api = null, $params = array()) {
		foreach (self::$triggers as $item) {
			if ($item[0] === $action && self::isEqual($api, $item[2], true)) {
				$event = new Event($action);
				$event->setData($item[3]);
				$event->setApi($item[2]);
				call_user_func_array($item[1], array_merge(array($event), $params));
			}
		}
	}
	
	private static function getVarGuid($var) {
		switch (true) {
			case (null === $var):
				return $var;
			case is_scalar($var):
				return (string)$var;
			case ($var instanceof Api):
				$guid = $var->getModelName();
				if ($var->isNew()) {
					$guid .= '_new';
				} elseif (!$var->isEmpty()) {
					$guid .= '_' . $var->id;
				}
				return $guid;
			case is_object($var):
				return spl_object_hash($var);
			case is_callable($var):
				return $var;
		}
		return $var;
	}
	
	private static function getCallback($callback, $api) {
		if (is_string($callback) && $api) {
			if (method_exists($api, $callback) || method_exists($api, 'action_' . $callback)) {
				return array($api, $callback);
			}
		}
		return $callback;
	}
	
	private static function isEqual($var1, $var2, $orNull = false) {
		if ($orNull && null === $var1)
			return true;
		
		if ($var1 instanceof Api) {
			if ($var1->getModelName() === $var2)
				return true;
		}
		return (self::getVarGuid($var1) === self::getVarGuid($var2));
	}
	
}