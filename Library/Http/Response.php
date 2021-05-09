<?php

namespace Http;

class Response {

	protected $encoding = null;
	protected $content = '';
	protected $range;
	protected $httpCode;
	protected $headers = [];

	public function setRawHeader(string $header, $replace = true, $code = null) {
		$this->headers[] = [$header, $replace, $code];
		return $this;
	}

	public function setHeader($name, string $header, $replace = true, $code = null) {
		$this->headers[$name] = [$name . ': ' . $header, $replace, $code];
		return $this;
	}

	public function setHeaderIf($name, $header, $replace = true, $code = null) {
		if (!isset($this->headers[$name]))
			$this->setHeader($name, $header, $replace, $code);
		return $this;
	}

	public function setContentType($type, $charset = false) {
		if ($charset === 'default' || $charset === true)
			$charset = 'utf-8';
		return $this->setHeader('Content-Type', $type . ($charset ? '; charset=' . $charset : ''));
	}

	public function setContentLength($length) {
		return $this->setHeader('Content-Length', $length);
	}

	public function setHttpCode($code) {
		$this->httpCode = $code;
		/*
		  $HTTPCodes = [
		  100 => 'Continue',
		  101 => 'Switching Protocols',
		  102 => 'Processing',
		  200 => 'OK',
		  201 => 'Created',
		  202 => 'Accepted',
		  203 => 'Non-Authoritative Information',
		  204 => 'No Content',
		  205 => 'Reset Content',
		  206 => 'Partial Content',
		  207 => 'Multi-Status',
		  300 => 'Multiple Choices',
		  301 => 'Moved Permanently',
		  302 => 'Found',
		  303 => 'See Other',
		  304 => 'Not Modified',
		  305 => 'Use Proxy',
		  306 => 'Switch Proxy',
		  307 => 'Temporary Redirect',
		  400 => 'Bad Request',
		  401 => 'Unauthorized',
		  402 => 'Payment Required',
		  403 => 'Forbidden',
		  404 => 'Not Found',
		  405 => 'Method Not Allowed',
		  406 => 'Not Acceptable',
		  407 => 'Proxy Authentication Required',
		  408 => 'Request Timeout',
		  409 => 'Conflict',
		  410 => 'Gone',
		  411 => 'Length Required',
		  412 => 'Precondition Failed',
		  413 => 'Request Entity Too Large',
		  414 => 'Request-URI Too Long',
		  415 => 'Unsupported Media Type',
		  416 => 'Requested Range Not Satisfiable',
		  417 => 'Expectation Failed',
		  418 => 'I\'m a teapot',
		  422 => 'Unprocessable Entity',
		  423 => 'Locked',
		  424 => 'Failed Dependency',
		  425 => 'Unordered Collection',
		  426 => 'Upgrade Required',
		  449 => 'Retry With',
		  450 => 'Blocked by Windows Parental Controls',
		  500 => 'Internal Server Error',
		  501 => 'Not Implemented',
		  502 => 'Bad Gateway',
		  503 => 'Service Unavailable',
		  504 => 'Gateway Timeout',
		  505 => 'HTTP Version Not Supported',
		  506 => 'Variant Also Negotiates',
		  507 => 'Insufficient Storage',
		  509 => 'Bandwidth Limit Exceeded',
		  510 => 'Not Extended'
		  ];
		  $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
		  header($protocol . ' ' . $code . ' ' . $HTTPCodes[$code], true, $code); */
		return $this;
	}

	public function setRange($range) {
		$this->range = $range;
		return $this;
	}

	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	public function getContent() {
		return $this->content;
	}

	public function setEncoding($encoding) {
		$this->encoding = $encoding;
		$this->setHeader('Content-Encoding', $encoding);
		return $this;
	}

	public function isHeadersSent() {
		return headers_sent();
	}

	public function sendHeaders() {
		if ($this->isHeadersSent())
			return $this;
		self::sendHttpCode($this->httpCode ?: 200);
		foreach ($this->headers as $value) {
			header($value[0], $value[1], $value[2]);
		}
		return $this;
	}

	public function send() {
		switch ($this->encoding) {
			default:
				$content = (string)$this->content;
		}

		$length = strlen($content);

		/*if ($this->range && preg_match('/(\w+)=(\d+)-(\d*)/', $this->range, $m)) {
			$start = $m[2];
			$end = ($m[3] ? $m[3] : ($length - 1));
			$this->setRawHeader('Accept-Ranges: ' . $m[1]);
			$this->setRawHeader('Content-Range: ' . $m[1] . ' ' . $start . '-' . $end . '/' . $length);
			$body = substr($body, $start, $end);
			$length = $end - $start;
		}*/

		$this->setContentLength($length);

		$this->sendHeaders();
		echo $content;
		return $this;
	}

	private static function sendHttpCode($code) {
		if (function_exists('http_response_code'))
			return http_response_code($code ?: 200);

		static $HTTPCodes = [
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Switch Proxy',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			418 => 'I\'m a teapot',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			425 => 'Unordered Collection',
			426 => 'Upgrade Required',
			449 => 'Retry With',
			450 => 'Blocked by Windows Parental Controls',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			509 => 'Bandwidth Limit Exceeded',
			510 => 'Not Extended'
		];

		$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

		header($protocol . ' ' . $code . ' ' . $HTTPCodes[$code], true, $code);
	}

}
