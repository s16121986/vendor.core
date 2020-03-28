<?php

namespace Exception\Log;

use Db;

class Email extends \Exception\Log {
	
	private $address = '';
	
	private $options = array(
		'subject' => 'PHP error_log message',
		'from' => '',
		'fromName' => 'debug'
	);
	
	public function __construct($address, $options = null) {
		$this->address = $address;
		if ($options) {
			$this->options = $options;
		}
	}
	
	public function __get($name) {
		return (isset($this->options[$name]) ? $this->options[$name] : null);
	}

	public function log($exception, $logType) {
		$formatter = \Exception\Output::factory('Log');
		$this->send($formatter->format($exception, true), $this->from);
		/*return;
		switch ($logType) {
			case self::uncaughtException:
				$formatter = \Exception\Output::factory('Log');
				$mail = new \Mail\Sender();
				$mail->addAddress($this->address);
				$mail->Subject = $this->subject;
				$from = $this->from;
				if (!$from) {
					$from = 'debug@' . $_SERVER['HTTP_HOST'];
				}
				$mail->From = $from;
				if ($this->fromName) {
					$mail->FromName = $this->fromName;
				}
				$mail->MsgHTML($formatter->format($exception));
				$mail->send();
				//$header = 'From: debug@2pr2.ru';// . "\n";
				//error_log($formatter->format($exception), 1, $this->address, $header);
				break;
		}*/
	}
	
	public function send($message, $from = null) {
		if (!$from) {
			$from = 'debug@' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'server');
		}
		$n = PHP_EOL;
		$header = 'MIME-Version: 1.0' . $n;
		$header .= 'Content-Type: text/html; charset=utf-8' . $n;
		$header .= 'From: ' . $from;
		error_log($message, 1, $this->address, $header);
	}

}