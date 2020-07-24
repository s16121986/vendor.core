<?php

namespace Db\Adapter;

use Db;
use Exception;
use Db\Adapter\Mysqli\Result;

class Mysqli {

	/**
	 * @var Profiler\ProfilerInterface
	 */
	protected $profiler = null;

	/**
	 * Connection parameters
	 *
	 * @var array
	 */
	protected $connectionParameters = array();

	/**
	 * @var \mysqli
	 */
	protected $resource = null;

	/**
	 * In transaction
	 *
	 * @var bool
	 */
	protected $inTransaction = false;

	public function __construct($connectionInfo) {
		if (is_array($connectionInfo)) {
			$this->setConnectionParameters($connectionInfo);
		} elseif ($connectionInfo instanceof \mysqli) {
			$this->setResource($connectionInfo);
		} elseif (null !== $connectionInfo) {
			throw new Exception\InvalidArgumentException('$connection must be an array of parameters, a mysqli object or null');
		}
		$this->connect();
	}

	/**
	 * Set connection parameters
	 *
	 * @param  array $connectionParameters
	 * @return Connection
	 */
	public function setConnectionParameters(array $connectionParameters) {
		$this->connectionParameters = $connectionParameters;
		return $this;
	}

	/**
	 * Get connection parameters
	 *
	 * @return array
	 */
	public function getConnectionParameters() {
		return $this->connectionParameters;
	}

	/**
	 * Set resource
	 *
	 * @param  \mysqli $resource
	 * @return Connection
	 */
	public function setResource(\mysqli $resource) {
		$this->resource = $resource;
		return $this;
	}

	/**
	 * Get resource
	 *
	 * @return \mysqli
	 */
	public function getResource() {
		$this->connect();
		return $this->resource;
	}

	/**
	 * Connect
	 *
	 * @throws Exception\RuntimeException
	 * @return Connection
	 */
	public function connect() {
		if ($this->isConnected()) {
			return $this;
		}

		// localize
		$p = $this->connectionParameters;

		// given a list of key names, test for existence in $p
		$findParameterValue = function (array $names) use ($p) {
			foreach ($names as $name) {
				if (isset($p[$name])) {
					return $p[$name];
				}
			}
			return;
		};

		$hostname = $findParameterValue(array('hostname', 'host'));
		$username = $findParameterValue(array('username', 'user'));
		$password = $findParameterValue(array('password', 'passwd', 'pw'));
		$database = $findParameterValue(array('database', 'dbname', 'db', 'schema'));
		$port = (isset($p['port'])) ? (int) $p['port'] : null;
		$socket = (isset($p['socket'])) ? $p['socket'] : null;
		if (false !== $findParameterValue(array('permanent'))) {
			$hostname = 'p:' . $hostname;
		}

		$this->resource = new \mysqli();
		$this->resource->init();

		$this->resource->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

		if (!empty($p['driver_options'])) {
			foreach ($p['driver_options'] as $option => $value) {
				if (is_string($option)) {
					$option = strtoupper($option);
					if (!defined($option)) {
						continue;
					}
					$option = constant($option);
				}
				$this->resource->options($option, $value);
			}
		}

		$flag = $this->resource->real_connect($hostname, $username, $password, $database, $port, $socket);
		if (!$flag || $this->resource->connect_error) {
			throw new Exception\RuntimeException(
					'Connection error', null, new Exception\ErrorException($this->resource->connect_error, $this->resource->connect_errno)
			);
		}

		if (isset($p['charset']) && !empty($p['charset']))
			$this->resource->set_charset($p['charset']);

		if (isset($p['timezone']))
			$this->resource->query('SET time_zone="' . $p['timezone'] . '"'); //SET time_zone = '-05:00';

		return $this;
	}

	/**
	 * Is connected
	 *
	 * @return bool
	 */
	public function isConnected() {
		return ($this->resource instanceof \mysqli);
	}

	/**
	 * Disconnect
	 *
	 * @return void
	 */
	public function disconnect() {
		if ($this->resource instanceof \mysqli) {
			$this->resource->close();
		}
		unset($this->resource);
	}

	/**
	 * Begin transaction
	 *
	 * @return void
	 */
	public function beginTransaction() {
		$this->connect();

		$this->resource->autocommit(false);
		$this->inTransaction = true;
	}

	public function startTransaction() {
		return $this->beginTransaction();
	}

	/**
	 * Commit
	 *
	 * @return void
	 */
	public function commit() {
		if (!$this->resource) {
			$this->connect();
		}

		$this->resource->commit();
		$this->inTransaction = false;
		$this->resource->autocommit(true);
	}

	/**
	 * Rollback
	 *
	 * @throws Exception\RuntimeException
	 * @return Connection
	 */
	public function rollback() {
		if (!$this->resource) {
			throw new Exception\RuntimeException('Must be connected before you can rollback.');
		}

		if (!$this->inTransaction) {
			throw new Exception\RuntimeException('Must call commit() before you can rollback.');
		}

		$this->resource->rollback();
		$this->resource->autocommit(true);
		return $this;
	}

	/**
	 * Create result
	 *
	 * @param resource $resource
	 * @param null|bool $isBuffered
	 * @return Result
	 */
	public function createResult($resource, $isBuffered = null) {
		$result = new Result();
		$result->initialize($resource, $this->getLastGeneratedValue(), $isBuffered);
		return $result;
	}

	/**
	 * Execute
	 *
	 * @param  string $sql
	 * @throws Exception\InvalidQueryException
	 * @return Result
	 */
	public function execute($sql) {
		$this->connect();

		if ($this->profiler) {
			$this->profiler->profilerStart($sql);
		}

		$resultResource = $this->resource->query($sql);

		if ($this->profiler) {
			$this->profiler->profilerFinish($sql);
		}

		// if the returnValue is something other than a mysqli_result, bypass wrapping it
		if ($resultResource === false) {
			throw new Exception($this->resource->error . '; query: ' . $sql);
		}
		return $this->createResult(($resultResource === true) ? $this->resource : $resultResource);
	}

	/**
	 * Get last generated id
	 *
	 * @param  null $name Ignored
	 * @return int
	 */
	public function getLastGeneratedValue($name = null) {
		return $this->resource->insert_id;
	}

	public function query($query) {
		return $this->execute($query);
	}

	public function write($table, $data, $where = null) {
		if ($where) {
			return $this->update($table, $data, $where);
		} else {
			return $this->insert($table, $data);
		}
	}

	public function writeArray($table, $array) {
		foreach ($array as $row) {
			if (isset($row['id'])) {
				if (!($id = (int) $row['id'])) {
					continue;
				}
				$where = array('id' => $id);
				/* if (is_string($where)) {
				  $wh = str_replace('%id', $row['id'], $where);
				  } else {
				  $wh = $id;
				  } */
				if (isset($row['delete'])) {
					$this->delete($table, $where);
				} else {
					$this->update($table, $row, $where);
				}
			} else {
				$this->insert($table, $row);
			}
		}
		return true;
	}

	public function insert($table, $data) {
		$this->connect();

		$values = $this->getValuesArray($data);

		$sql = 'INSERT INTO `' . $table . '` (' . join(',', array_keys($values)) . ') VALUES (' . join(',', array_values($values)) . ')';
		$this->query($sql);
		if ($this->resource->affected_rows) {
			$id = $this->getLastGeneratedValue();
			return ($id ? $id : true);
		}
		return false;
	}

	public function update($table, $data, $where) {
		$this->connect();

		$values = $this->getValuesArray($data);
		$array = array();
		foreach ($values as $k => $v) {
			$array[] = $k . '=' . $v;
		}
		if ((is_int($where) || (is_string($where) && preg_match('/^\d+$/', $where)))) {
			$id = (int) $where;
			$where = array();
			$where['id'] = $id;
		}
		$sql = 'UPDATE `' . $table . '` SET ' . join(',', $array) . $this->getWhereQuery($where);

		$this->query($sql);

		if ($this->resource->affected_rows > -1) {
			return isset($where['id']) ? $where['id'] : true;
		}
		return false;
	}

	public function delete($table, $where) {
		$sql = 'DELETE FROM `' . $table . '`' . $this->getWhereQuery($where);
		$this->query($sql);
		//if ($this->resource->affected_rows) {
		//}
		return true;
	}

	public function quoteValue($value) {
		if ($this->resource instanceof \mysqli) {
			return null === $value ? 'NULL' : '\'' . $this->resource->real_escape_string($value) . '\'';
		}
		trigger_error(
				'Attempting to quote a value in ' . __CLASS__ . ' without extension/driver support '
				. 'can introduce security vulnerabilities in a production environment.'
		);
		return null === $value ? 'NULL' : '\'' . addcslashes($value, "\x00\n\r\\'\"\x1a") . '\'';
	}

	public function getTableFields($tableName) {
		return $this->query('SHOW FIELDS FROM `' . $tableName . '`')->fetchAll(Db::FETCH_NAMED, 'Field');
	}

	private function getValuesArray($data) {
		$array = array();
		foreach ($data as $key => $value) {
			$value = $this->formatValue($value);
			if (null !== $value) {
				$array['`' . $key . '`'] = $value;
			}
		}
		return $array;
	}

	private function formatValue($value) {
		switch (true) {
			case is_int($value):
			case $value === 'CURRENT_TIMESTAMP':
				return $value;
			case is_bool($value):
				return $value ? 1 : 0;
			case is_float($value):
				return str_replace(',', '.', (string) $value);
			case is_scalar($value):
				return $this->quoteValue($value);
			case null === $value:
				return 'NULL';
			case $value instanceof Db\Expr:
				return (string) $value;
			case $value instanceof \DateTime:

				break;
		}
		return null;
	}

	private function formatFieldValue($value, $field) {
		if (null === $value && $field['Null'] === 'YES') {
			return 'NULL';
		}
		$p = strpos($field['Type'], '(');
		$l = 0;
		if ($p === false) {
			$type = $field['Type'];
		} else {
			$type = substr($field['Type'], 0, $p);
			$l = substr($field['Type'], $p + 1, -1);
		}
		$fieldValue = null;
		switch ($type) {
			case 'char':
			case 'varchar':
			case 'text':
			case 'smalltext':
			case 'mediumtext':
			case 'longtext':
			case 'longblob':
				$fieldValue = self::format_string($value);
				break;
			case 'enum':
				$items = array();
				foreach (explode(',', $l) as $item) {
					$items[] = trim($item, '\'');
				}
				if (in_array($value, $items)) {
					$fieldValue = $value;
				}
				break;
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
				$fieldValue = self::format_integer($value);
				break;
			case 'float':
			case 'decimal':
			case 'double':
				$fieldValue = self::format_float($value);
				break;
			case 'datetime':
				$fieldValue = self::format_date($value, 'Y-m-d H:i:s');
				break;
			case 'date':
				$fieldValue = self::format_date($value, 'Y-m-d');
				break;
			case 'time':
				$fieldValue = self::format_date($value, 'H:i:s');
				break;
			case 'timestamp':
				if ($value === 'CURRENT_TIMESTAMP') {
					$fieldValue = $value;
				} else {
					$fieldValue = self::format_date($value, 'Y-m-d H:i:s');
					if ($fieldValue === null && $field['Null'] != 'YES') {
						$fieldValue = 'CURRENT_TIMESTAMP';
					}
				}
				break;
		}
		if (null === $fieldValue) {
			if ($field['Null'] === 'YES') {
				return 'NULL';
			}
			return null;
		}
		return $this->quoteValue($fieldValue);
	}

	private function getWhereQuery($where) {
		if (!$where) {
			return '';
		}
		if (is_string($where) && !preg_match('/^\d+$/', $where)) {
			return ' WHERE ' . $where;
		} elseif (!is_array($where)) {
			$where = array('id' => (int) $where);
		}
		$array = array();
		foreach ($this->getValuesArray($where) as $k => $v) {
			$array[] = $k . ($v === 'NULL' ? ' IS NULL' : '=' . $v);
		}
		return ' WHERE ' . implode(' AND ', $array);
	}

	private static function getPrimaryKey($columns) {
		foreach ($columns as $k => $column) {
			if ($column['Key'] == 'PRI') {
				return $k;
			}
		}
		return null;
	}

	private static function format_integer($value) {
		return (is_numeric($value) ? (int) $value : null);
	}

	private static function format_float($value) {
		return (is_numeric($value) ? (float) $value : null);
	}

	private static function format_string($value) {
		return (is_scalar($value) ? (string) $value : null);
	}

	private static function format_date($value, $format) {
		if (is_string($value)) {
			$time = strtotime($value);
			if ($time <= 0) {
				return null;
			}
		} elseif ($value instanceof \DateTime) {
			$time = $value->getTimestamp();
		} elseif (is_int($value)) {
			$time = $value;
		} else {
			return null;
		}
		return date($format, $time);
	}

}
