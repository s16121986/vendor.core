<?php

namespace Exception\Log;

class File extends \Exception\Log {

	public function log($exception, $logType) {
		switch ($logType) {
			case self::uncaughtException:
				$formatter = new \Exception\Output\Log('');
				error_log($formatter->format($exception));
				break;
		}
	}

}