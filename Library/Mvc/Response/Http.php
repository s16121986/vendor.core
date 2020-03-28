<?php
namespace Mvc\Response;

use Http\Response as HttpResponse;
use Http\Util as HttpUtil;

class Http extends HttpResponse{

	protected $HTTP_ACCEPT_ENCODING = true;
    protected $renderExceptions = false;
	protected $exception = null;
	
	public function __construct() {
		$this->init();
	}
	
	protected function init() {
		$this->setHeader('Date', self::gmt(now()->getTimestamp()), true);
	}
	
	public function renderExceptions($flag = null) {
		if (null === $flag)
			return $this->renderExceptions;
		$this->renderExceptions = $flag;
		return $this;
	}
	
	public function hasException() {
		return (bool)$this->exception;
	}
	
	public function setException($exception) {
		$this->exception = $exception;
		return $this;
	}
	
	public function getException() {
		return $this->exception;
	}
	
	public function cacheControl($lastModified, $redirect = true) {
		if (is_array($lastModified)) {
			$temp = $lastModified;
			$lastModified = null;
			foreach ($temp as $date) {
				if ($date > $lastModified)
					$lastModified = $date;
			}
		}
		$timestamp = strtotime($lastModified);
		$this->setHeader('Cache-Control:', 'public, must-revalidate', true);
		$this->setHeader('Last-Modified', self::gmt($timestamp), true);
		if ($redirect) {
			$modified = HttpUtil::getHeader('If-Modified-Since');
			if ($modified && (strtotime($modified) >= $timestamp)) {
				$this->setHttpCode(304);
				$this->sendHeaders();
				exit;
			}
		}
		$now = now();
		$now->modify('+1 month');
		$this->setHeader('Expires', self::gmt($now->getTimestamp()), true);
	}
	
	public function setRedirect($url, $code = 301) {
		$this
			->setHeader('Location', $url, true)
            ->setHttpCode($code)
			->sendHeaders();
		exit;
	}

	public function send() {
		if ($this->hasException()) {
			if ($this->renderExceptions()) {
				$this->setContent($this->exception->__toString());
			}
			$this->setHttpCode($this->exception->getCode());
		} else {
			$this->setHttpCode(200);
		}
		return parent::send();
	}
	
	protected static function gmt($timestamp) {
		return gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
	}
	
}