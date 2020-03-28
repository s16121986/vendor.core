<?php
namespace Auth\Provider;

use SimpleXMLElement;
use stdClass;

class Response{
	
	const FORMAT_AUTO = 'auto';
	const FORMAT_STRING = 'string';
	const FORMAT_JSON = 'json';
	const FORMAT_XML = 'xml';
	
	protected $format = 'auto';
	protected $response;
	protected $httpCode;
	protected $httpInfo;
	protected $data = null;
	
	public function __construct($response) {
		$this->response = $response;
	}
	
	public function __get($name) {
		$data = $this->getData();
		if (isset($data->$name)) {
			return $data->$name;
		}
		if (isset($this->$name)) {
			return $this->$name;
		}
	}
	
	public function __set($name, $value) {
		if ($this->data) {
			$this->data->$name = $value;
		}
	}
	
	public function getData() {
		if (null === $this->data) {
			$this->data = self::parseResponse($this->response, $this->format);
		}
		return $this->data;
	}
	
	public function setFormat($format) {
		$this->format = $format;
		return $this;
	}
	
	public function setHttpCode($code) {
		$this->httpCode = $code;
		return $this;
	}
	
	public function setHttpInfo($info) {
		$this->httpInfo = $info;
		if (isset($info['content_type'])) {
			switch (true) {
				case (0 === strpos($info['content_type'], 'application/json')):
					$this->setFormat(self::FORMAT_JSON);
					break;
				case (0 === strpos($info['content_type'], 'text/plain')):
					//$this->setFormat(self::FORMAT_JSON);
					break;
			}
		}
		return $this;
	}

	private static function parseResponse($response, $format = self::FORMAT_AUTO) {
		if (is_array($response)) {
			return $response;
		} elseif (is_object($response)) {
			return self::parse_xml($response);
		}
		switch ($format) {
			case self::FORMAT_AUTO:
				if (is_string($response)) {
					if (0 === strpos($response, '<?xml ')) {
						return self::parseResponse($response, self::FORMAT_XML);
					}
					$data = self::parseResponse($response, self::FORMAT_JSON);
					if (!$data) {
						$data = self::parseResponse($response, self::FORMAT_STRING);
					}
					return $data;
				}
				break;
			case self::FORMAT_JSON:
				return json_decode($response);
			case self::FORMAT_XML:
				$xml = new SimpleXMLElement($response);
				return self::parse_xml($xml);
			case self::FORMAT_STRING:
				$output = array();
				parse_str($response, $output);
				return self::parse_xml($output);
		}
		return false;
	}
	
	private static function parse_xml($xml) {
		$object = new stdClass();
		foreach ($xml as $k => $v) {
			if (is_array($v)) {
				$object->$k = self::parse_xml($v);
			} else {
				$object->$k = (string)$v;
			}
		}
		return $object;
	}
	
}