<?php
namespace Api\Util;

use Db;
use Api;
use Api\Exception;
use Api\Util\Reference\ForeignKey;

class DeleteManager{

	const DEBUG = false;

	private static $fileAttributes = array();
	private static $started = false;
	private static $api = null;

	public static function initialized() {
		return (null === self::$api);
	}

	public static function init($api) {
		if (null === self::$api) {
			self::$api = $api;
			self::startTransaction();
			return true;
		}
		return false;
	}

	public static function startTransaction() {
		if (!self::$started) {
			Db::query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
			self::$started = true;
		}
	}

	public static function commit() {
		Db::query('COMMIT');
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
		self::$fileAttributes = [];
		self::$started = false;
	}

	public static function delete() {
		if (self::_delete(self::$api)) {
			foreach (self::$fileAttributes as $attribute) {
				$attribute->delete();
			}
			return true;
		}
		return false;
	}

	public static function pushFileAttribute($attribute) {
		self::$fileAttributes[] = $attribute;
	}
	
	private static function _delete($api) {
		if (($records = self::getRestrictRecords($api))) {
			throw new Exception(Exception::DELETE_ABORTED, array('links' => $records));
		}
		/*foreach ($api->getReferences() as $reference) {
			switch ($reference->onDelete) {
				case Reference::CASCADE:
					self::queueDelete($reference);
					break;
				case Reference::SETNULL:
					self::queueSetNull($reference);
					break;
			}
		}*/
		return $api->delete();
	}
	
	private static function queueSetNull($reference) {
		switch ($reference->type) {
			case Reference::MODEL:
				foreach (self::getReferenceRecords($reference) as $r) {
					$api = $reference->getModel();
					if ($api->findById($r['id'])) {
						$api->{$reference->foreignKey} = null;
						$api->write();
					}
				}
				break;
			case Reference::TABLE:
				Db::update($reference->name, array($reference->foreignKey => null), self::getConditionParams($reference));
				break;
		}
	}
	
	private static function queueDelete($reference) {
		switch ($reference->type) {
			case Reference::MODEL:
				foreach (self::getReferenceRecords($reference) as $r) {
					$api = $reference->getModel();
					if ($api->findById($r['id'])) {
						self::_delete($api);
					}
				}
				break;
			case Reference::TABLE:
				Db::delete($reference->name, self::getConditionParams($reference));
				break;
		}
	}
	
	private static function getReferenceRecords($reference) {
		$params = self::getConditionParams($reference);
		switch ($reference->type) {
			case Reference::MODEL:
				$linkApi = Api::factory($reference->name);
				$params['fields'] = array('id');
				return $linkApi->select($params);
			case Reference::TABLE:
				$sql = Db::from($reference->name);
				foreach ($params as $k => $v) {
					$sql->where($k . '=?', $v);
				}
				return $sql->query()->fetchAll();
		}
		return false;
	}
	
	private static function getRestrictRecords($api) {
		/*foreach ($api->getReferences() as $reference) {
			if ($reference->onDelete === Reference::RESTRICT) {
				if (($records = self::getReferenceRecords($reference))) {
					return $records;
				}
			}
		}*/
		return false;
	}
	
	private static function getConditionParams($reference) {
		$params = array();
		$foreignKey = $reference->foreignKey;
		if (!is_array($foreignKey)) {
			$foreignKey = array($foreignKey);
		}
		foreach ($foreignKey as $k => $v) {
			if ($v instanceof ForeignKey) {
				$params[$v->name] = $v->getValue($reference->api);
			} elseif (0 === $k) {
				$params[$v] = $reference->api->id;
			} else {
				$params[$k] = $v;
			}
		}
		return $params;
	}

}