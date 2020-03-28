<?php
use Db\Adapter\Mysqli as Adapter;

class Db{
	
	const FETCH_ASSOC = 2;
    const FETCH_BOTH = 4;
    const FETCH_BOUND = 6;
    const FETCH_CLASS = 8;
    const FETCH_CLASSTYPE = 262144;
    const FETCH_COLUMN = 7;
    const FETCH_FUNC = 10;
    const FETCH_GROUP = 65536;
    const FETCH_INTO = 9;
    const FETCH_LAZY = 1;
    const FETCH_NAMED = 11;
    const FETCH_NUM = 3;
    const FETCH_OBJ = 5;
    const FETCH_ORI_ABS = 4;
    const FETCH_ORI_FIRST = 2;
    const FETCH_ORI_LAST = 3;
    const FETCH_ORI_NEXT = 0;
    const FETCH_ORI_PRIOR = 1;
    const FETCH_ORI_REL = 5;
    const FETCH_SERIALIZE = 524288;
    const FETCH_UNIQUE = 196608;
	
	private static $adapter;
	
	public static function setConfig($config) {
		return self::init($config);
	}
	
	public static function init($connectionInfo) {
		self::$adapter = new Adapter($connectionInfo);
		register_shutdown_function(function() {
			Db::getAdapter()->disconnect();
		});
		return self::$adapter;
	}
	
	public static function getAdapter() {
		return self::$adapter;
	}
	
	public static function query($query) {
		return self::$adapter->query($query);
	}
	
	public static function from($name, $columns = '*') {
		$select = new Db\Select(self::$adapter);
		return $select->from($name, $columns);
	}

	public static function select($from, $where) {
		return Db::from($from)
				->where($where)
				->query()->fetchAll();
	}

	public static function insert($table, $data) {
		return self::write($table, $data);
	}

	public static function update($table, $data, $where) {
		return self::write($table, $data, $where);
	}

	public static function write($table, $data, $where = null) {
		return self::$adapter->write($table, $data, $where);
	}
	
	public static function writeArray($table, $data) {
		return self::$adapter->writeArray($table, $data);
	}

	public static function delete($table, $where) {
		return self::$adapter->delete($table, $where);
	}
	
	public static function getTableFields($table) {
		return self::$adapter->getTableFields($table);
	}
	
	public static function quote($value) {
		return self::$adapter->quoteValue($value);
	}
	
}