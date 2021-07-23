<?php

namespace Exception;

abstract class Handler {

	public static $debug = false;

	/**
	 * if true ignores @-operator and generates exception
	 * if false donesn't generate exceptions for errors slashed with @-operator, but logs it
	 * @var bool
	 */
	public static $scream = false;

	/**
	 * Which type of error convert to exception
	 * @var int
	 */
	public static $errorTypesException = E_ALL;

	/**
	 * @var bool
	 */
	public static $assertionThrowException = true;

	/**
	 * @var int
	 */
	public static $assertionErrorType = E_USER_ERROR;

	/**
	 * If seted to instanse of ExceptionHandlerLog, will log
	 * If seted to null will not log
	 * @var ExceptionHandlerLog
	 */
	public static $exceptionLog = null;

	/**
	 * @var exceptionHandlerOutput
	 */
	private static $exceptionOutput;

	/**
	 * @var array
	 */
	private static $previousAssertOptions;

	/**
	 * @var bool
	 */
	private static $setupFlag = false;

	/**
	 * @param string $error_log
	 */
	public static function setupEnvironment($error_log = null) {
		if (self::$debug) {
			ini_set('error_reporting', E_ALL | E_STRICT);
			ini_set('display_errors', 'On');
			ini_set('display_startup_errors', 'On');
		} else {
			ini_set('error_reporting', 0); //E_ERROR
			ini_set('display_errors', 'Off');
			ini_set('display_startup_errors', 'Off');
		}

		ini_set('html_errors', 'Off');
		ini_set('docref_root', '');
		ini_set('docref_ext', '');

		ini_set('log_errors', 'On');
		ini_set('log_errors_max_len', 0);
		ini_set('ignore_repeated_errors', 'Off');
		ini_set('ignore_repeated_source', 'Off');
		ini_set('report_memleaks', 'Off');
		ini_set('track_errors', 'On');
		ini_set('xmlrpc_errors', 'Off');
		ini_set('xmlrpc_error_number', 'Off');
		ini_set('error_prepend_string', '');
		ini_set('error_append_string', '');
		if ($error_log) {
			ini_set('error_log', $error_log);
		}
	}

	/**
	 * setup handlers
	 *
	 * @param exceptionHandlerOutput $exceptionHandlerOutput
	 * @param int $errorTypesHandle wich errors will be converted to exceptions
	 */
	public static function setupHandlers($exceptionOutput = null, $errorTypesHandle = null) {
		if (is_null($errorTypesHandle)) {
			$errorTypesHandle = E_ALL | E_STRICT;
		}

		self::$exceptionOutput = \Exception\Output::factory($exceptionOutput);

		\Exception\Output::$exceptionHandlerClass = __CLASS__;
		\Exception\Output::setUtf();

		if (!self::$setupFlag) {
			set_error_handler(__CLASS__ . '::errorHandler', $errorTypesHandle);
			set_exception_handler(__CLASS__ . '::exceptionHandler');
			register_shutdown_function(__CLASS__ . '::shutdownHandler');
			self::$previousAssertOptions[ASSERT_ACTIVE] = assert_options(ASSERT_ACTIVE);
			self::$previousAssertOptions[ASSERT_WARNING] = assert_options(ASSERT_ACTIVE);
			self::$previousAssertOptions[ASSERT_BAIL] = assert_options(ASSERT_BAIL);
			//self::$previousAssertOptions[ASSERT_QUIET_EVAL] = assert_options(ASSERT_QUIET_EVAL);
			self::$previousAssertOptions[ASSERT_CALLBACK] = assert_options(ASSERT_CALLBACK);
			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 0);
			assert_options(ASSERT_BAIL, 0);
			//assert_options(ASSERT_QUIET_EVAL, 0);
			assert_options(ASSERT_CALLBACK, __CLASS__ . '::assertionHandler');
			self::$setupFlag = true;
		}
	}

	/**
	 * Restores error, exception and assertion handlers
	 */
	public static function restoreHandlers() {
		if (self::$setupFlag) {
			restore_error_handler();
			restore_exception_handler();
			assert_options(ASSERT_ACTIVE, self::$previousAssertOptions[ASSERT_ACTIVE]);
			assert_options(ASSERT_WARNING, self::$previousAssertOptions[ASSERT_WARNING]);
			assert_options(ASSERT_BAIL, self::$previousAssertOptions[ASSERT_BAIL]);
			//assert_options(ASSERT_QUIET_EVAL, self::$previousAssertOptions[ASSERT_QUIET_EVAL]);
			assert_options(ASSERT_CALLBACK, self::$previousAssertOptions[ASSERT_CALLBACK]);
			self::$setupFlag = false;
		}
	}

	public static function shutdownHandler() {
		$error = error_get_last();
		if (isset($error)) {
			if ($error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR || $error['type'] == E_CORE_ERROR) {
				$exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
				self::exceptionHandler($exception);
			}
		}
	}

	/**
	 * Handles uncaught exceptions
	 *
	 * @param Exception $exception
	 */
	public static function exceptionHandler($exception) {
		self::exceptionLog($exception, \Exception\Log::uncaughtException);
		self::$exceptionOutput->output($exception, self::$debug);
	}

	/**
	 * Convert error to exception
	 *
	 * @param int $severity The severity level of the exception
	 * @param string $message The Exception message to throw
	 * @param string $file The filename where the exception is thrown
	 * @param int $line The filename where the exception is thrown
	 * @throws ErrorException
	 */
	public static function errorHandler($severity, $message, $file, $line) {
		$exception = new \ErrorException($message, 0, $severity, $file, $line);

		if ($severity & self::$errorTypesException) {
			throw $exception;
		}
		/**
		 * don't throw exception if '@' operator used
		 */
		if (error_reporting() === 0 && !self::$scream) {
			self::exceptionLog($exception, \Exception\Log::ignoredError);
			return;
		} else if (!($severity & self::$errorTypesException)) {
			self::exceptionLog($exception, \Exception\Log::lowPriorityError);
			return;
		}
	}

	/**
	 * Convert assertion fail to exception
	 *
	 * @param string $file The filename where the exception is thrown
	 * @param int $line The filename where the exception is thrown
	 * @param string $message The Exception message to throw
	 * @throws ErrorException
	 */
	public static function assertionHandler($file, $line, $message) {
		$exception = new ErrorException($message, 0, self::$assertionErrorType, $file, $line);
		if (!self::$assertionThrowException) {
			self::exceptionLog($exception, \Exception\Log::assertion);
			return;
		}
		throw $exception;
	}

	/**
	 * @param Exception $exception
	 * @param int $logPriority
	 */
	public static function exceptionLog($exception, $logPriority = null) {
		if (!is_null(self::$exceptionLog)) {
			self::$exceptionLog->log($exception, $logPriority);
		}
	}

}
