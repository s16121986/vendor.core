<?php
namespace Http\Content;

use Http\Util as HttpUtil;
use File;

abstract class AbstractContent{
	
	const MINUTE = 60;
	const HOUR = 3600;
	const DAY = 86400;
	const MONTH = 2592000;
	const YEAR = 31536000;
	
	protected $fileExtension = '*';
	protected $includePath = '';
	protected $encoding = false;
	protected $headers = [];
	protected $content = [];
	protected $params = [];
	
	abstract protected function init();
	
	public function __construct() {
		$this->init();
	}
	
	public function set($name, $value) {
		$this->params[$name] = $value;
		return $this;
	}
	
	public function get($name) {
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
	
	public function setEncoding($encoding) {
		$this->encoding = $encoding;
		return $this;
	}
	
	public function enableEncoding() {
		$this->setEncoding(HttpUtil::getHeader('Accept-Encoding'));
		return $this;
	}
	
	public function enableCache($destination, $time = null) {
		if ($time === null) {
			$time = self::YEAR;
		}
		$this->setHeader('Cache-Control', 'public, max-age=' . $time . ', must-revalidate');
		//$this->addHeader('Pragma', 'private');
		$this->setHeader('Expires', date('r', time() + $time));

		$fileModTime = filemtime($destination);

		$modified = HttpUtil::getHeader('If-Modified-Since');

		if ($modified && (strtotime($modified) == $fileModTime)) {
			$this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 304);
			$this->sendHeaders();
			exit;
		}
		$this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $fileModTime) . ' GMT', true, 200);
		return $this;
	}

	public function setIncludePath($path) {
		$this->includePath = $path;
		return $this;
	}
	
	public function setHeader($name, $header, $replace = true, $code = null) {
		$this->headers[$name] = [$header, $replace, $code];
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
	
	public function addFile($file) {
		if (is_array($file)) {
			foreach ($file as $fp) {
				$this->addFile($fp);
			}
		} elseif (is_string($file)) {
			if (isset($this->content[$file])) {
				return $this;
			}
			$this->content[$file] = file_get_contents($this->includePath . $file);
		} elseif ($file instanceof File) {
			$this->addContent($file->getData());
		}
		return $this;
	}
	
	public function addDir($dir, $deep = true) {
		if (is_array($dir)) {
			foreach ($dir as $d) {
				$this->addDir($d);
			}
			return $this;
		}
		$path = $this->includePath . rtrim($dir, DIRECTORY_SEPARATOR);
		$deepDirs = [];
		$dh = opendir($path);
		while (false !== ($file = readdir($dh))) {
			if (in_array($file, ['.', '..']))
				continue;
			$filepath = $path . DIRECTORY_SEPARATOR . $file;
			if (is_dir($filepath)) {
				if ($deep) {
					$deepDirs[] = $dir . DIRECTORY_SEPARATOR . $file;
				}
			} else {
				if ('*' === $this->fileExtension || substr($file, strrpos($file, '.')) == ('.' . $this->fileExtension)) {
					if (!file_exists($filepath)) {
						die($filepath);
					}
					$this->addFile($dir . DIRECTORY_SEPARATOR . $file);
				}
			}
		}
		foreach ($deepDirs as $dir) {
			$this->addDir($dir, $deep);
		}
		return $this;
	}
	
	public function setContent($content) {
		$this->content = [$content];
		return $this;
	}
	
	public function addContent($content) {
		$this->content[] = $content;
		return $this;
	}

	public function getContent() {
		return implode("\n", $this->content);
	}
	
	public function sendHeaders() {
		foreach ($this->headers as $name => $value) {
			header($name . ': ' . $value[0], $value[1], $value[2]);
		}
	}
	
	public function out() {
		$content = $this->getContent();
		if ($this->encoding) {
			foreach (explode(',', $this->encoding) as $encoding) {
				$encoding = strtolower(trim($encoding));
				$encoded = self::gzencode($encoding, $content);
				if (false === $encoded)
					continue;
				$content = $encoded;
				$this->setHeader('Content-Encoding', $encoding);
				break;
			}
		}
		if (!isset($this->headers['Content-Length']))
			$this->setHeader('Content-Length', strlen($content));
		$this->sendHeaders();
		die($content);
	}
	
	private static function gzencode($encoding, $content) {
		switch ($encoding) {
			case 'gzip': return gzencode($content, 9);
			case 'deflate': return gzdeflate($content, 9);
		}
		return false;
	}
	
}