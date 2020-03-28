<?php

namespace Db\Adapter\Mysqli;

use Db;
use Exception;

class Result {

	/**
	 * @var \mysqli|\mysqli_result|\mysqli_stmt
	 */
	protected $resource = null;

	/**
	 * @var bool
	 */
	protected $isBuffered = null;

	/**
	 * Cursor position
	 * @var int
	 */
	protected $position = 0;

	/**
	 * Number of known rows
	 * @var int
	 */
	protected $numberOfRows = -1;

	/**
	 * Is the current() operation already complete for this pointer position?
	 * @var bool
	 */
	protected $currentComplete = false;

	/**
	 * @var bool
	 */
	protected $nextComplete = false;

	/**
	 * @var bool
	 */
	protected $currentData = false;

	/**
	 *
	 * @var array
	 */
	protected $statementBindValues = array('keys' => null, 'values' => array());

	/**
	 * @var mixed
	 */
	protected $generatedValue = null;
	protected $fetchMode = Db::FETCH_ASSOC;

	/**
	 * Initialize
	 *
	 * @param mixed $resource
	 * @param mixed $generatedValue
	 * @param bool|null $isBuffered
	 * @throws Exception\InvalidArgumentException
	 * @return Result
	 */
	public function initialize($resource, $generatedValue, $isBuffered = null) {
		if (!$resource instanceof \mysqli && !$resource instanceof \mysqli_result && !$resource instanceof \mysqli_stmt) {
			throw new Exception\InvalidArgumentException('Invalid resource provided.');
		}

		if ($isBuffered !== null) {
			$this->isBuffered = $isBuffered;
		} else {
			if ($resource instanceof \mysqli || $resource instanceof \mysqli_result || $resource instanceof \mysqli_stmt && $resource->num_rows != 0) {
				$this->isBuffered = true;
			}
		}

		$this->resource = $resource;
		$this->generatedValue = $generatedValue;
		return $this;
	}

	/**
	 * Get resource
	 *
	 * @return mixed
	 */
	public function getResource() {
		return $this->resource;
	}

	/**
	 * Is query result?
	 *
	 * @return bool
	 */
	public function isQueryResult() {
		return ($this->resource->field_count > 0);
	}

	public function fetch($style = null, $free = false) {
		if (!$this->isQueryResult()) {
			return false;
		}
		$result = $this->_fetch($style);
		if ($free) {
			$this->resource->free();
		}
		return $result;
	}
	
	private function _fetch($style) {
		// make sure we have a fetch mode
		if ($style === null) {
			$style = $this->fetchMode;
		}

		$row = false;
		switch ($style) {
			case Db::FETCH_NUM:
				$row = $this->resource->fetch_row();
				break;
			case Db::FETCH_ASSOC:
				$row = $this->resource->fetch_assoc();
				break;
			case Db::FETCH_BOTH:
				$row = $this->resource->fetch_array();
				break;
			case Db::FETCH_OBJ:
				$row = (object) $this->resource->fetch_object($this->_queryId);
				break;
			/* case Db::FETCH_BOUND:
			  $assoc = array_combine($this->_keys, $values);
			  $row = array_merge($values, $assoc);
			  return $this->_fetchBound($row);
			  break; */
			default:
				throw new Exception("Invalid fetch mode '$style' specified");
		}
		return $row;
	}

	public function fetchRow($style = null, $free = true) {
		if (!$this->isQueryResult()) {
			return false;
		}
		$result = $this->_fetch($style);
		if ($free) {
			$this->resource->free();
		}
		return $result;
	}

	/**
	 * Returns an array containing all of the result set rows.
	 *
	 * @param int $style OPTIONAL Fetch mode.
	 * @param int $col   OPTIONAL Column number, if fetch mode is by column.
	 * @return array Collection of rows, each in a format by the fetch mode.
	 */
	public function fetchAll($style = null, $col = null) {
		if (!$this->isQueryResult()) {
			return false;
		}
		$data = array();
		if ($style === Db::FETCH_COLUMN && $col === null) {
			$col = 0;
		}
		if ($style === Db::FETCH_NAMED && $col) {
			while ($row = $this->_fetch(Db::FETCH_ASSOC)) {
				$data[$row[$col]] = $row;
			}
		} elseif ($col === null) {
			while ($row = $this->_fetch($style)) {
				$data[] = $row;
			}
		} else {
			while ($row = $this->_fetch(Db::FETCH_NUM)) {
				$data[] = (isset($row[$col]) ? $row[$col] : null);
			}
		}
		$this->resource->free();
		return $data;
	}

	/**
	 * Returns a single column from the next row of a result set.
	 *
	 * @param int $col OPTIONAL Position of the column to fetch.
	 * @return string One value from the next row of result set, or false.
	 */
	public function fetchColumn($col = 0, $free = true) {
		if (!$this->isQueryResult()) {
			return false;
		}
		$col = (int) $col;
		$row = $this->_fetch(Db::FETCH_NUM);
		if (!is_array($row) || !isset($row[$col])) {
			return false;
		}
		if ($free) {
			$this->resource->free();
		}
		return $row[$col];
	}

	/**
	 * Count
	 *
	 * @throws Exception\RuntimeException
	 * @return int
	 */
	public function count() {
		if ($this->isBuffered === false) {
			throw new Exception\RuntimeException('Row count is not available in unbuffered result sets.');
		}
		return $this->resource->num_rows;
	}

}
