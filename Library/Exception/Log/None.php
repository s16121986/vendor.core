<?php

namespace Exception\Log;

class None extends \Exception\Log {

	public function log($exception, $logType) {
		echo '<pre>', $exception->getTraceAsString(), '</pre>';
		return;
		switch ($logType) {
			case self::uncaughtException:
				$formatter = new exceptionHandlerOutputCli('');
				error_log($formatter->format($exception));
				break;
		}
	}

}