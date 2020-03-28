<?php

namespace Exception;

abstract class Output {

	public static $exceptionHandlerClass;

	/**
	 * @var string
	 */
	public static $productionMessage = 'Internal Server Error';
	public static $productionFile = null;

	/**
	 * @var int
	 */
	public static $maxchars = 40;

	/**
	 * @var bool
	 */
	protected static $utf = false;

	public static function factory($output = null) {
		if ($output) {
			$class = '\\Exception\\Output\\' . $output;
			if (!class_exists($class)) {
				$output = null;
			}
		}
		if (!$output) {
			$output = 'Html';
		}
		$class = '\\Exception\\Output\\' . $output;
		return new $class();
	}

	/**
	 * @param int $severity
	 */
	public static function severityToString($severity) {
		switch ($severity) {
			case 1: return 'E_ERROR';
				break;
			case 2: return 'E_WARNING';
				break;
			case 4: return 'E_PARSE';
				break;
			case 8: return 'E_NOTICE';
				break;
			case 16: return 'E_CORE_ERROR';
				break;
			case 32: return 'E_CORE_WARNING';
				break;
			case 64: return 'E_COMPILE_ERROR';
				break;
			case 128: return 'E_COMPILE_WARNING';
				break;
			case 256: return 'E_USER_ERROR';
				break;
			case 512: return 'E_USER_WARNING';
				break;
			case 1024: return 'E_USER_NOTICE';
				break;
			case 2048: return 'E_STRICT';
				break;
			case 4096: return 'E_RECOVERABLE_ERROR';
				break;
			case 8192: return 'E_DEPRECATED';
				break;
			case 16384: return 'E_USER_DEPRECATED';
				break;
			case 30719: return 'E_ALL';
				break;
		}
	}

	/**
	 * @return bool
	 */
	public static function setUtf() {
		if (extension_loaded('mbstring')) {
			self::$utf = (ini_get('mbstring.internal_encoding') == 'UTF-8');
		}
		return self::$utf;
	}

	/**
	 * @param Exception $exception
	 */
	public static function getMessage($exception) {
		$result = '';
		if ($exception instanceof \Api\Exception) {
			return $exception->getMessage() . "\nData:" . print_r($exception->getData(), true);
		} elseif ($exception instanceof ErrorException) {
			$result = self::severityToString($exception->getSeverity());
			if (strlen($exception->getMessage())) {
				$result .= ' - ';
			}
		}
		$result .= $exception->getMessage();
		return $result;
	}

	/**
	 * @param Exception $exception
	 * @return array
	 */
	public static function getTrace($exception) {
		$backtrace = $exception->getTrace();
		$count = count($backtrace);
		/**
		 * bug
		 * @see http://www.php.net/manual/en/class.errorexception.php#86985
		 */
		if (strpos(phpversion(), '5.2') === 0 && $exception instanceof ErrorException) {
			for ($i = $count - 1; $i > 0; --$i) {
				$backtrace[$i]['args'] = $backtrace[$i - 1]['args'];
			}
			$backtrace[0]['args'] = null;
		}
		$result = array();
		for ($i = 0; $i < $count; $i++) {
			if (array_key_exists('class', $backtrace[$i]) && $backtrace[$i]['class'] == self::$exceptionHandlerClass) {
				continue;
			}
			$result[] = $backtrace[$i];
		}
		return $result;
	}

	/**
	 * @param mixed $arg
	 * @return string utf-8
	 */
	public static function argToString($arg) {
		switch (gettype($arg)) {
			case 'boolean':
				$arg = $arg ? 'true' : 'false';
				break;
			case 'NULL':
				$arg = 'null';
				break;
			case 'integer':
			case 'double':
			case 'float':
				$arg = (string) $arg;
				if (self::$utf) {
					$arg = str_replace('INF', '∞', $arg); //is_infinite($arg)
				}
				break;
			case 'string':
				if (is_callable($arg, false, $callable_name)) {
					$arg = 'fs:' . $callable_name;
				} else if (class_exists($arg, false)) {
					$arg = 'c:' . $arg;
				} else if (interface_exists($arg, false)) {
					$arg = 'i:' . $arg;
				} else {
					$strlen = self::stringLength($arg);
					$arg = self::formatString($arg);
					if ($strlen <= self::$maxchars) {
						$arg = '"' . $arg . '"';
					} else {
						$arg = '"' . $arg . '"(' . $strlen . ')';
					}
					return $arg = str_replace("\n", '\n', $arg);
				}
				break;
			case 'array':
				if (is_callable($arg, false, $callable_name)) {
					$arg = 'fa:' . $callable_name;
				} else {
					$arg = 'array(' . count($arg) . ')';
				}
				break;
			case 'object':
				$arg = get_class($arg) . '()'; //.':'.spl_object_hash($arg);
				break;
			case 'resource':
				// @see http://php.net/manual/en/resource.php
				$arg = 'r:' . get_resource_type($arg);
				break;
			default:
				$arg = 'unknown type';
				break;
		}
		return $arg;
	}

	/**
	 * @param string $str
	 * @return string
	 */
	public static function formatString($str) {
		if (self::stringLength($str) > self::$maxchars) {
			if (self::$utf) {
				$hellip = '…';
				$str = trim(mb_substr($str, 0, self::$maxchars / 2)) . $hellip . trim(mb_substr($str, -self::$maxchars / 2));
			} else {
				$hellip = '...';
				$str = substr($str, 0, self::$maxchars / 2) . $hellip . substr($str, -self::$maxchars / 2);
			}
		}
		return $str;
	}

	/**
	 * @param string $str
	 * @return int
	 */
	public static function stringLength($str) {
		if (self::$utf) {
			$strlen = mb_strlen($str);
		} else {
			$strlen = strlen($str);
		}
		return $strlen;
	}

	/**
	 * @param array $args
	 * @return string
	 */
	public static function argumentsToString($args) {
		if (!is_null($args)) {
			foreach ($args as $iArg => $arg) {
				$args[$iArg] = self::argToString($arg);
			}
			return '(' . implode(', ', $args) . ')';
		}
		return '()';
	}

	/**
	 * @see http://bugs.php.net/bug.php?id=50921
	 * @param Exception $exception
	 * @param bool $debug
	 */
	public function output($exception, $debug) {
		if (!headers_sent()) {
			header('HTTP/1.0 500 Internal Server Error', true, 500);
			header('Status: 500 Internal Server Error', true, 500);
		}
		if ($debug) {
			exit($this->format($exception));
		} else {
			exit(self::$productionFile ? file_get_contents(self::$productionFile) : self::$productionMessage);
		}
	}

	/**
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	protected function getFileLink($file, $line) {
		if (is_null($file)) {
			return '    unknown file';
		}
		return '    ' . $file . ':' . $line;
	}

	protected function _format($exception, $html = false) {
		$aTrace = self::getTrace($exception);
		$sTrace = '[' . get_class($exception) . ']: ';
		$sTrace .= self::getMessage($exception) . ' in ' . $exception->getFile() . ':' . $exception->getLine() . "\n";
		if ($html) {
			if (self::$utf) {
				$sTrace = htmlspecialchars($sTrace, ENT_COMPAT, 'UTF-8');
			} else {
				$sTrace = htmlspecialchars($sTrace);
			}
		}
		$prevArg = null;
		foreach ($aTrace as $aTraceNo => $aTraceLine) {
			$sTraceLine = '#' . $aTraceNo . ': ';
			if (array_key_exists('class', $aTraceLine)) {
				$sTraceLine .= $aTraceLine['class'] . $aTraceLine['type'];
			}
			if (array_key_exists('function', $aTraceLine)) {
				$sTraceLine .= $aTraceLine['function'];
				if (!in_array($aTraceLine['function'], array('mysql_connect', 'mysql_pconnect', 'real_connect', 'mysqli::real_connect'))) {
					$sTraceLine .= self::argumentsToString(isset($aTraceLine['args']) ? $aTraceLine['args'] : null);
				}
			}
			if ($html) {
				if (self::$utf) {
					$sTraceLine = htmlspecialchars($sTraceLine, ENT_COMPAT, 'UTF-8');
				} else {
					$sTraceLine = htmlspecialchars($sTraceLine);
				}
			}
			if (array_key_exists('file', $aTraceLine)) {
				$sTraceLine .= "\n" . $this->getFileLink(
								$aTraceLine['file'], $aTraceLine['line']);
			} else {
				$sTraceLine .= "\n" . $this->getFileLink(null, null);
			}
			$sTrace .= $sTraceLine . "\n";
		}
		if ($html) {
			$sTrace = '<pre>' . $sTrace . '</pre>';
		}
		return $sTrace;
	}

	/**
	 * @param Exception $exception
	 */
	abstract public function format($exception);
}