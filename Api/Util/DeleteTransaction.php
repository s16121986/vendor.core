<?php
namespace Api\Util;

use Db;
use Api\Exception;
use Api\Attribute\AttributeFile;
use Exception as BaseException;

abstract class DeleteTransaction{

	private static $cache = [];
	private static $files = [];
	private static $started = false;
	private static $forced = false;
	private static $api = null;

	public static function initialized() {
		return (null !== self::$api);
	}

	public static function init($api, $forced = false) {
		self::$forced = $forced;
		self::$api = $api;
		
		self::start();

		try {
			
			if (false === self::delete())
				throw new Exception('Delete aborted', Exception::DELETE_ABORTED, $api);
			
		} catch (BaseException $e) {
			self::rollback();
			throw $e;
		}
		
		self::commit();

		return true;
	}

	public static function start() {
		if (self::$started)
			return;
		
		Db::query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
		self::$started = true;
	}

	public static function commit() {		
		Db::query('COMMIT');
		foreach (self::$files as $file) {
			$file->delete();
		}
		self::reset();
		return true;
	}

	public static function rollback() {
		Db::query('ROLLBACK');
		self::reset();
		return true;
	}

	public static function reset() {
		self::$api = null;
		self::$files = [];
		self::$started = false;
		self::$forced = false;
		self::$cache = [];
	}

	public static function delete() {
		return self::_delete(self::$api);
	}

	public static function pushFile($file) {
		if (is_array($file)) {
			foreach ($file as $fr) {
				self::pushFile($fr);
			}
			return;
		}
		if (!$file || !$file->id)
			return;
		self::$files[] = $file;
	}
	
	private static function _delete($api) {
		
		$uid = self::id($api);
		if (in_array($uid, self::$cache))
			return true;
		
		self::$cache[] = $uid;
		
		$relations = $api->getRelations();
		if (!self::$forced && $relations->hasRestricted())
			throw new Exception('Delete aborted', Exception::DELETE_RESTRICTED, $relations);
		
		foreach ($relations as $relation) {
			self::_delete($relation->entity);
		}
			
		foreach ($api->getAttributes() as $attribute) {
			if  ($attribute instanceof AttributeFile) {
				self::pushFile($attribute->getValue());
			}
		}
		
		return $api->delete();
	}
	
	private static function id($api) {
		return $api->getModelName() . '_' . $api->id;
	}

}