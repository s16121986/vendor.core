<?php

namespace Exception\Output;

class System extends \Exception\Output {

	/**
	 * @var string
	 */
	protected static $fileLinkFormat;

	/**
	 * This setting determines the format of the links that are made in the
	 * display of stack traces where file names are used. This allows IDEs to
	 * set up a link-protocol that makes it possible to go directly to a line
	 * and file by clicking on the filenames that shows in stack traces.
	 * An example format might look like:
	 * 'txmt://open/?file://%f&line=%l' (TextMate)
	 * 'gvim://%f@%l' (gVim - with additional hack)
	 * 'nb://%f:%l' (NetBeans - with additional hack)
	 * The possible format specifiers are:
	 * %f - the filename
	 * %l - the line number
	 *
	 * @see https://bugs.eclipse.org/bugs/show_bug.cgi?id=305345
	 * @see http://code.google.com/p/coda-protocol/
	 *
	 * @param string $fileLinkFormat
	 */
	public static function setFileLinkFormat($fileLinkFormat) {
		ini_set('xdebug.file_link_format', self::$fileLinkFormat);
		self::$fileLinkFormat = $fileLinkFormat;
	}

	/**
	 * @var string
	 */
	protected $_fileLinkFormat;

	/**
	 * @param string $fileLinkFormat
	 */
	public function __construct($fileLinkFormat = null) {
		if (is_null($fileLinkFormat)) {
			$this->_fileLinkFormat = self::$fileLinkFormat;
		} else {
			$this->_fileLinkFormat = $fileLinkFormat;
		}
	}

	/**
	 * @param string $file
	 * @param int $line
	 * @return string
	 */
	protected function getFileLink($file, $line) {
		if (is_null($file) || !strlen($this->_fileLinkFormat)) {
			return parent::getFileLink($file, $line);
		}
		$fileLink = str_replace(array('%f', '%l'), array(urlencode($file), $line), $this->_fileLinkFormat);
		return '    <a href="' . $fileLink . '">' . self::formatString(parent::getFileLink($file, $line)) . '</a>';
	}

	/**
	 * @param Exception $exception
	 * @return string
	 */
	public function format($exception) {
		return $this->_format($exception, false);
	}

}