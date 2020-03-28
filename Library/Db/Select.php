<?php
namespace Db;

use Db;
use Exception;

class Select{

	const DISTINCT       = 'distinct';
    const COLUMNS        = 'columns';
    const FROM           = 'from';
    const UNION          = 'union';
    const WHERE          = 'where';
    const GROUP          = 'group';
    const HAVING         = 'having';
    const ORDER          = 'order';
    const LIMIT_COUNT    = 'limitcount';
    const LIMIT_OFFSET   = 'limitoffset';
    const FOR_UPDATE     = 'forupdate';

    const INNER_JOIN     = 'inner join';
    const LEFT_JOIN      = 'left join';
    const RIGHT_JOIN     = 'right join';
    const FULL_JOIN      = 'full join';
    const CROSS_JOIN     = 'cross join';
    const NATURAL_JOIN   = 'natural join';

    const SQL_WILDCARD   = '*';
    const SQL_SELECT     = 'SELECT';
    const SQL_UNION      = 'UNION';
    const SQL_UNION_ALL  = 'UNION ALL';
    const SQL_FROM       = 'FROM';
    const SQL_WHERE      = 'WHERE';
    const SQL_DISTINCT   = 'DISTINCT';
    const SQL_GROUP_BY   = 'GROUP BY';
    const SQL_ORDER_BY   = 'ORDER BY';
    const SQL_HAVING     = 'HAVING';
    const SQL_FOR_UPDATE = 'FOR UPDATE';
    const SQL_AND        = 'AND';
    const SQL_AS         = 'AS';
    const SQL_OR         = 'OR';
    const SQL_ON         = 'ON';
    const SQL_ASC        = 'ASC';
    const SQL_DESC       = 'DESC';

    /**
     * Bind variables for query
     *
     * @var array
     */
    protected $_bind = array();

    /**
     * Db_Adapter_Abstract object.
     *
     * @var Db_Adapter_Abstract
     */
    protected $_adapter;

    /**
     * The initial values for the $_parts array.
     * NOTE: It is important for the 'FOR_UPDATE' part to be last to ensure
     * meximum compatibility with database adapters.
     *
     * @var array
     */
    protected static $_partsInit = array(
        self::DISTINCT     => false,
        self::COLUMNS      => array(),
        self::UNION        => array(),
        self::FROM         => array(),
        self::WHERE        => array(),
        self::GROUP        => array(),
        self::HAVING       => array(),
        self::ORDER        => array(),
        self::LIMIT_COUNT  => null,
        self::LIMIT_OFFSET => null,
        self::FOR_UPDATE   => false
    );

    /**
     * Specify legal join types.
     *
     * @var array
     */
    protected static $_joinTypes = array(
        self::INNER_JOIN,
        self::LEFT_JOIN,
        self::RIGHT_JOIN,
        self::FULL_JOIN,
        self::CROSS_JOIN,
        self::NATURAL_JOIN,
    );

    /**
     * Specify legal union types.
     *
     * @var array
     */
    protected static $_unionTypes = array(
        self::SQL_UNION,
        self::SQL_UNION_ALL
    );

    /**
     * The component parts of a SELECT statement.
     * Initialized to the $_partsInit array in the constructor.
     *
     * @var array
     */
    protected $_parts = array();

    /**
     * Tracks which columns are being select from each table and join.
     *
     * @var array
     */
    protected $_tableCols = array();

	public function __construct($adapter) {
		$this->_adapter = $adapter;
		$this->_parts = self::$_partsInit;
	}

	public function distinct($flag = true) {
        $this->_parts[self::DISTINCT] = (bool) $flag;
        return $this;
    }

	public function from($name, $cols = '*', $schema = null) {
		return $this->_join(self::FROM, $name, null, $cols, $schema);
	}

	public function columns($cols = '*', $correlationName = null) {
        if ($correlationName === null && count($this->_parts[self::FROM])) {
            $correlationNameKeys = array_keys($this->_parts[self::FROM]);
            $correlationName = current($correlationNameKeys);
        }

        if (!array_key_exists($correlationName, $this->_parts[self::FROM])) {
            /**
             * @see Db_Select_Exception
             */
            //require_once 'Library/Db/Select/Exception.php';
            throw new Exception("No table has been specified for the FROM clause");
        }

        $this->_tableCols($correlationName, $cols);

        return $this;
    }

    public function union($select = array(), $type = self::SQL_UNION) {
        if (!is_array($select)) {
            require_once 'Library/Db/Select/Exception.php';
            throw new Db_Select_Exception(
                "union() only accepts an array of Db_Select instances of sql query strings."
            );
        }

        if (!in_array($type, self::$_unionTypes)) {
            require_once 'Library/Db/Select/Exception.php';
            throw new Db_Select_Exception("Invalid union type '{$type}'");
        }

        foreach ($select as $target) {
            $this->_parts[self::UNION][] = array($target, $type);
        }

        return $this;
    }

    public function join($name, $cond, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->joinInner($name, $cond, $cols, $schema);
    }

    public function joinInner($name, $cond, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::INNER_JOIN, $name, $cond, $cols, $schema);
    }

    public function joinLeft($name, $cond, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::LEFT_JOIN, $name, $cond, $cols, $schema);
    }

    public function joinRight($name, $cond, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::RIGHT_JOIN, $name, $cond, $cols, $schema);
    }

    public function joinFull($name, $cond, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::FULL_JOIN, $name, $cond, $cols, $schema);
    }

    public function joinCross($name, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::CROSS_JOIN, $name, null, $cols, $schema);
    }

    public function joinNatural($name, $cols = self::SQL_WILDCARD, $schema = null) {
        return $this->_join(self::NATURAL_JOIN, $name, null, $cols, $schema);
    }

	public function where($cond, $value = null, $type = null) {
		if (is_array($cond)) {
			foreach ($cond as $k => $v) {
				if (null === $v)
					$this->where($cond . ' IS NULL');
				else
					$this->where($cond . '=?', $v);
			}
		} else {
			$this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, true);
		}
        return $this;
	}

	public function orWhere($cond, $value = null, $type = null) {
        $this->_parts[self::WHERE][] = $this->_where($cond, $value, $type, false);

        return $this;
    }

	public function group($spec)
    {
        if (!is_array($spec)) {
            $spec = array($spec);
        }

        foreach ($spec as $val) {
            if (preg_match('/\(.*\)/', (string) $val)) {
                $val = new Db\Expr($val);
            }
            $this->_parts[self::GROUP][] = $val;
        }

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by AND.
     *
     * If a value is passed as the second param, it will be quoted
     * and replaced into the condition wherever a question-mark
     * appears. See {@link where()} for an example
     *
     * @param string $cond The HAVING condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param int      $type  OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     */
    public function having($cond, $value = null, $type = null)
    {
        if ($value !== null) {
            $cond = $this->quoteInto($cond, $value, $type);
        }

        if ($this->_parts[self::HAVING]) {
            $this->_parts[self::HAVING][] = self::SQL_AND . " ($cond)";
        } else {
            $this->_parts[self::HAVING][] = "($cond)";
        }

        return $this;
    }

    /**
     * Adds a HAVING condition to the query by OR.
     *
     * Otherwise identical to orHaving().
     *
     * @param string $cond The HAVING condition.
     * @param mixed    $value OPTIONAL The value to quote into the condition.
     * @param int      $type  OPTIONAL The type of the given value
     * @return Zend_Db_Select This Zend_Db_Select object.
     *
     * @see having()
     */
    public function orHaving($cond, $value = null, $type = null)
    {
        if ($value !== null) {
            $cond = $this->quoteInto($cond, $value, $type);
        }

        if ($this->_parts[self::HAVING]) {
            $this->_parts[self::HAVING][] = self::SQL_OR . " ($cond)";
        } else {
            $this->_parts[self::HAVING][] = "($cond)";
        }

        return $this;
    }

	public function order($spec) {
        if (!is_array($spec)) {
            $spec = array($spec);
        }

        // force 'ASC' or 'DESC' on each order spec, default is ASC.
        foreach ($spec as $val) {
            if ($val instanceof Db\Expr) {
                $expr = $val->__toString();
                if (empty($expr)) {
                    continue;
                }
                $this->_parts[self::ORDER][] = $val;
            } else {
                if (empty($val)) {
                    continue;
                }
                $direction = self::SQL_ASC;
                if (preg_match('/(.*\W)(' . self::SQL_ASC . '|' . self::SQL_DESC . ')\b/si', $val, $matches)) {
                    $val = trim($matches[1]);
                    $direction = $matches[2];
                }
                if (preg_match('/\(.*\)/', $val)) {
                    $val = new Db\Expr($val);
                }
                $this->_parts[self::ORDER][] = array($val, $direction);
            }
        }

        return $this;
    }

	public function limit($count = null, $offset = null) {
        $this->_parts[self::LIMIT_COUNT]  = (int) $count;
        $this->_parts[self::LIMIT_OFFSET] = (int) $offset;
        return $this;
    }

    public function limitPage($page, $rowCount) {
        $page     = ($page > 0)     ? $page     : 1;
        $rowCount = ($rowCount > 0) ? $rowCount : 1;
        $this->_parts[self::LIMIT_COUNT]  = (int) $rowCount;
        $this->_parts[self::LIMIT_OFFSET] = (int) $rowCount * ($page - 1);
        return $this;
    }

	protected function _tableCols($correlationName, $cols, $afterCorrelationName = null) {
        if (!is_array($cols)) {
            $cols = array($cols);
        }

        if ($correlationName == null) {
            $correlationName = '';
        }

        $columnValues = array();

        foreach (array_filter($cols) as $alias => $col) {
            $currentCorrelationName = $correlationName;
            if (is_string($col)) {
                // Check for a column matching "<column> AS <alias>" and extract the alias name
                if (preg_match('/^(.+)\s+' . self::SQL_AS . '\s+(.+)$/i', $col, $m)) {
                    $col = $m[1];
                    $alias = $m[2];
                }
                // Check for columns that look like functions and convert to Db\Expr
                if (preg_match('/\(.*\)/', $col)) {
                    $col = new Db\Expr($col);
                } elseif (preg_match('/(.+)\.(.+)/', $col, $m)) {
                    $currentCorrelationName = $m[1];
                    $col = $m[2];
                }
            }
            $columnValues[] = array($currentCorrelationName, $col, is_string($alias) ? $alias : null);
        }

        if ($columnValues) {

            // should we attempt to prepend or insert these values?
            if ($afterCorrelationName === true || is_string($afterCorrelationName)) {
                $tmpColumns = $this->_parts[self::COLUMNS];
                $this->_parts[self::COLUMNS] = array();
            } else {
                $tmpColumns = array();
            }

            // find the correlation name to insert after
            if (is_string($afterCorrelationName)) {
                while ($tmpColumns) {
                    $this->_parts[self::COLUMNS][] = $currentColumn = array_shift($tmpColumns);
                    if ($currentColumn[0] == $afterCorrelationName) {
                        break;
                    }
                }
            }

            // apply current values to current stack
            foreach ($columnValues as $columnValue) {
                array_push($this->_parts[self::COLUMNS], $columnValue);
            }

            // finish ensuring that all previous values are applied (if they exist)
            while ($tmpColumns) {
                array_push($this->_parts[self::COLUMNS], array_shift($tmpColumns));
            }
        }
    }

	protected function _join($type, $name, $cond, $cols, $schema = null) {
        if (!in_array($type, self::$_joinTypes) && $type != self::FROM) {
            /**
             * @see Db_Select_Exception
             */
            require_once 'Library/Db/Select/Exception.php';
            throw new Db_Select_Exception("Invalid join type '$type'");
        }

        if (count($this->_parts[self::UNION])) {
            require_once 'Library/Db/Select/Exception.php';
            throw new Db_Select_Exception("Invalid use of table with " . self::SQL_UNION);
        }

        if (empty($name)) {
            $correlationName = $tableName = '';
        } else if (is_array($name)) {
            // Must be array($correlationName => $tableName) or array($ident, ...)
            foreach ($name as $_correlationName => $_tableName) {
                if (is_string($_correlationName)) {
                    // We assume the key is the correlation name and value is the table name
                    $tableName = $_tableName;
                    $correlationName = $_correlationName;
                } else {
                    // We assume just an array of identifiers, with no correlation name
                    $tableName = $_tableName;
                    $correlationName = $this->_uniqueCorrelation($tableName);
                }
                break;
            }
        } else if ($name instanceof Db\Expr|| $name instanceof Db_Select) {
            $tableName = $name;
            $correlationName = $this->_uniqueCorrelation('t');
        } else if (preg_match('/^(.+)\s+AS\s+(.+)$/i', $name, $m)) {
            $tableName = $m[1];
            $correlationName = $m[2];
        } else {
            $tableName = $name;
            $correlationName = $this->_uniqueCorrelation($tableName);
        }

        // Schema from table name overrides schema argument
        if (!is_object($tableName) && false !== strpos($tableName, '.')) {
            list($schema, $tableName) = explode('.', $tableName);
        }

        $lastFromCorrelationName = null;
        if (!empty($correlationName)) {
            if (array_key_exists($correlationName, $this->_parts[self::FROM])) {
                /**
                 * @see Db_Select_Exception
                 */
                require_once 'Library/Db/Select/Exception.php';
                throw new Db_Select_Exception("You cannot define a correlation name '$correlationName' more than once");
            }

            if ($type == self::FROM) {
                // append this from after the last from joinType
                $tmpFromParts = $this->_parts[self::FROM];
                $this->_parts[self::FROM] = array();
                // move all the froms onto the stack
                while ($tmpFromParts) {
                    $currentCorrelationName = key($tmpFromParts);
                    if ($tmpFromParts[$currentCorrelationName]['joinType'] != self::FROM) {
                        break;
                    }
                    $lastFromCorrelationName = $currentCorrelationName;
                    $this->_parts[self::FROM][$currentCorrelationName] = array_shift($tmpFromParts);
                }
            } else {
                $tmpFromParts = array();
            }
            $this->_parts[self::FROM][$correlationName] = array(
                'joinType'      => $type,
                'schema'        => $schema,
                'tableName'     => $tableName,
                'joinCondition' => $cond
                );
            while ($tmpFromParts) {
                $currentCorrelationName = key($tmpFromParts);
                $this->_parts[self::FROM][$currentCorrelationName] = array_shift($tmpFromParts);
            }
        }

        // add to the columns from this joined table
        if ($type == self::FROM && $lastFromCorrelationName == null) {
            $lastFromCorrelationName = true;
        }
		//if ($cols)
        $this->_tableCols($correlationName, $cols, $lastFromCorrelationName);

        return $this;
    }

	private function _uniqueCorrelation($name) {
        if (is_array($name)) {
            $c = end($name);
        } else {
            // Extract just the last name of a qualified table name
            $dot = strrpos($name,'.');
            $c = ($dot === false) ? $name : substr($name, $dot+1);
        }
        for ($i = 2; array_key_exists($c, $this->_parts[self::FROM]); ++$i) {
            $c = $name . '_' . (string) $i;
        }
        return $c;
    }

	protected function _where($condition, $value = null, $type = null, $bool = true) {
        if (count($this->_parts[self::UNION])) {
            require_once 'Library/Db/Select/Exception.php';
            throw new Db_Select_Exception("Invalid use of where clause with " . self::SQL_UNION);
        }

        if ($value !== null) {
            $condition = $this->quoteInto($condition, $value, $type);
        }

        $cond = "";
        if ($this->_parts[self::WHERE]) {
            if ($bool === true) {
                $cond = self::SQL_AND . ' ';
            } else {
                $cond = self::SQL_OR . ' ';
            }
        }

        return $cond . "($condition)";
    }

	public function query($fetchMode = null) {
		return $this->_adapter->query($this);
	}

	public function assemble() {
        $sql = self::SQL_SELECT;
        foreach (array_keys(self::$_partsInit) as $part) {
            $method = '_render' . ucfirst($part);
            if (method_exists($this, $method)) {
                $sql = $this->$method($sql);
            }
        }
        return $sql;
    }

	public function reset($part = null) {
        if ($part == null) {
            $this->_parts = self::$_partsInit;
        } else if (array_key_exists($part, self::$_partsInit)) {
            $this->_parts[$part] = self::$_partsInit[$part];
        }
        return $this;
    }

	public function getAdapter() {
        return $this->_adapter;
    }

	protected function _getDummyTable() {
        return array();
    }

    protected function _getQuotedSchema($schema = null) {
        if ($schema === null) {
            return null;
        }
        return $this->quoteIdentifier($schema, true) . '.';
    }

    protected function _getQuotedTable($tableName, $correlationName = null) {
        return $this->quoteTableAs($tableName, $correlationName, true);
    }

	protected function _renderDistinct($sql) {
        if ($this->_parts[self::DISTINCT]) {
            $sql .= ' ' . self::SQL_DISTINCT;
        }

        return $sql;
    }

    /**
     * Render DISTINCT clause
     *
     * @param string   $sql SQL query
     * @return string|null
     */
    protected function _renderColumns($sql) {
        if (!count($this->_parts[self::COLUMNS])) {
            return null;
        }

        $columns = array();
        foreach ($this->_parts[self::COLUMNS] as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($column instanceof Db\Expr) {
                $columns[] = $this->quoteColumnAs($column, $alias, true);
            } else {
                if ($column == self::SQL_WILDCARD) {
                    $column = new Db\Expr(self::SQL_WILDCARD);
                    $alias = null;
                }
                if (empty($correlationName)) {
                    $columns[] = $this->quoteColumnAs($column, $alias, true);
                } else {
                    $columns[] = $this->quoteColumnAs(array($correlationName, $column), $alias, true);
                }
            }
        }

        return $sql .= ' ' . implode(', ', $columns);
    }

    /**
     * Render FROM clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderFrom($sql) {
        /*
         * If no table specified, use RDBMS-dependent solution
         * for table-less query.  e.g. DUAL in Oracle.
         */
        if (empty($this->_parts[self::FROM])) {
            $this->_parts[self::FROM] = $this->_getDummyTable();
        }

        $from = array();

        foreach ($this->_parts[self::FROM] as $correlationName => $table) {
            $tmp = '';

            $joinType = ($table['joinType'] == self::FROM) ? self::INNER_JOIN : $table['joinType'];

            // Add join clause (if applicable)
            if (! empty($from)) {
                $tmp .= ' ' . strtoupper($joinType) . ' ';
            }

            $tmp .= $this->_getQuotedSchema($table['schema']);
            $tmp .= $this->_getQuotedTable($table['tableName'], $correlationName);

            // Add join conditions (if applicable)
            if (!empty($from) && ! empty($table['joinCondition'])) {
                $tmp .= ' ' . self::SQL_ON . ' ' . $table['joinCondition'];
            }

            // Add the table name and condition add to the list
            $from[] = $tmp;
        }

        // Add the list of all joins
        if (!empty($from)) {
            $sql .= ' ' . self::SQL_FROM . ' ' . implode("\n", $from);
        }

        return $sql;
    }

    protected function _renderUnion($sql) {
        if ($this->_parts[self::UNION]) {
            $parts = count($this->_parts[self::UNION]);
            foreach ($this->_parts[self::UNION] as $cnt => $union) {
                list($target, $type) = $union;
                if ($target instanceof Db_Select) {
                    $target = $target->assemble();
                }
                $sql .= $target;
                if ($cnt < $parts - 1) {
                    $sql .= ' ' . $type . ' ';
                }
            }
        }

        return $sql;
    }

    protected function _renderWhere($sql) {
        if ($this->_parts[self::FROM] && $this->_parts[self::WHERE]) {
            $sql .= ' ' . self::SQL_WHERE . ' ' .  implode(' ', $this->_parts[self::WHERE]);
        }

        return $sql;
    }

    /**
     * Render GROUP clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderGroup($sql) {
        if ($this->_parts[self::FROM] && $this->_parts[self::GROUP]) {
            $group = array();
            foreach ($this->_parts[self::GROUP] as $term) {
                $group[] = $this->quoteIdentifier($term, true);
            }
            $sql .= ' ' . self::SQL_GROUP_BY . ' ' . implode(",\n\t", $group);
        }

        return $sql;
    }

    /**
     * Render HAVING clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderHaving($sql) {
        if ($this->_parts[self::FROM] && $this->_parts[self::HAVING]) {
            $sql .= ' ' . self::SQL_HAVING . ' ' . implode(' ', $this->_parts[self::HAVING]);
        }

        return $sql;
    }

    /**
     * Render ORDER clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderOrder($sql) {
        if ($this->_parts[self::ORDER]) {
            $order = array();
            foreach ($this->_parts[self::ORDER] as $term) {
                if (is_array($term)) {
                    if(is_numeric($term[0]) && strval(intval($term[0])) == $term[0]) {
                        $order[] = (int)trim($term[0]) . ' ' . $term[1];
                    } else {
                        $order[] = $this->quoteIdentifier($term[0], true) . ' ' . $term[1];
                    }
                } else if (is_numeric($term) && strval(intval($term)) == $term) {
                    $order[] = (int)trim($term);
                } else {
                    $order[] = $this->quoteIdentifier($term, true);
                }
            }
            $sql .= ' ' . self::SQL_ORDER_BY . ' ' . implode(', ', $order);
        }

        return $sql;
    }

    /**
     * Render LIMIT OFFSET clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderLimitoffset($sql) {
        $count = 0;
        $offset = 0;

        if (!empty($this->_parts[self::LIMIT_OFFSET])) {
            $offset = (int) $this->_parts[self::LIMIT_OFFSET];
            $count = PHP_INT_MAX;
        }

        if (!empty($this->_parts[self::LIMIT_COUNT])) {
            $count = (int) $this->_parts[self::LIMIT_COUNT];
        }

        /*
         * Add limits clause
         */
        if ($count > 0) {
            $sql = trim($this->_limit($sql, $count, $offset));
        }

        return $sql;
    }

    /**
     * Render FOR UPDATE clause
     *
     * @param string   $sql SQL query
     * @return string
     */
    protected function _renderForupdate($sql) {
        if ($this->_parts[self::FOR_UPDATE]) {
            $sql .= ' ' . self::SQL_FOR_UPDATE;
        }

        return $sql;
    }
	
	

	private function _limit($sql, $count, $offset = 0) {
        $count = intval($count);
        if ($count <= 0) {
            throw new Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            throw new Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }

	protected function quote($value, $type = null) {
		if ($value instanceof Select) {
            return '(' . $value->assemble() . ')';
        }

        if ($value instanceof Db\Expr) {
            return $value->__toString();
        }

        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val, $type);
            }
            return implode(', ', $value);
        }
       return $this->_adapter->quoteValue($value);
    }

    public function quoteInto($text, $value, $type = null, $count = null) {
        if ($count === null) {
            return str_replace('?', $this->quote($value, $type), $text);
        } else {
            while ($count > 0) {
                if (strpos($text, '?') !== false) {
                    $text = substr_replace($text, $this->quote($value, $type), strpos($text, '?'), 1);
                }
                --$count;
            }
            return $text;
        }
    }

	public function quoteTableAs($ident, $alias = null, $auto = false) {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

	public function quoteColumnAs($ident, $alias, $auto=false) {
        return $this->_quoteIdentifierAs($ident, $alias, $auto);
    }

	public function quoteIdentifier($ident, $auto=false) {
        return $this->_quoteIdentifierAs($ident, null, $auto);
    }

    protected function _quoteIdentifier($value, $auto=false) {
		$q = '`';
		return ($q . str_replace("$q", "$q$q", $value) . $q);
    }

	protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ') {
        if ($ident instanceof Db\Expr) {
            $quoted = $ident->__toString();
        } elseif ($ident instanceof Select) {
            $quoted = '(' . $ident->assemble() . ')';
        } else {
            if (is_string($ident)) {
                $ident = explode('.', $ident);
            }
            if (is_array($ident)) {
                $segments = array();
                foreach ($ident as $segment) {
                    if ($segment instanceof Db\Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->_quoteIdentifier($segment, $auto);
                    }
                }
                if ($alias !== null && end($ident) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->_quoteIdentifier($ident, $auto);
            }
        }
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias, $auto);
        }
        return $quoted;
    }

	public function __toString() {
        try {
            $sql = $this->assemble();
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            $sql = '';
        }
        return (string)$sql;
    }
}