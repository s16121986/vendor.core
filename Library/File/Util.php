<?php

namespace File;

abstract class Util {

	public static function getMimeType(AbstractFile $file) {
		$finfo = new \finfo(FILEINFO_MIME_TYPE);

		if ($file->content)
			return $finfo->buffer($file->content);
		else if ($file->tmp_name)
			return $finfo->file($file->tmp_name);
		else if ($file->fullname && $file->exists())
			return $finfo->file($file->fullname);

		return null;
	}

	public static function getSize(AbstractFile $file) {
		return filesize($file->fullname);
	}

	private static function _mime_types($ext = '') {
		$mimes = array(
			'xl' => 'application/excel',
			'hqx' => 'application/mac-binhex40',
			'cpt' => 'application/mac-compactpro',
			'bin' => 'application/macbinary',
			'doc' => 'application/msword',
			'word' => 'application/msword',
			'class' => 'application/octet-stream',
			'dll' => 'application/octet-stream',
			'dms' => 'application/octet-stream',
			'exe' => 'application/octet-stream',
			'lha' => 'application/octet-stream',
			'lzh' => 'application/octet-stream',
			'psd' => 'application/octet-stream',
			'sea' => 'application/octet-stream',
			'so' => 'application/octet-stream',
			'oda' => 'application/oda',
			'pdf' => 'application/pdf',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			'smi' => 'application/smil',
			'smil' => 'application/smil',
			'mif' => 'application/vnd.mif',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'wbxml' => 'application/vnd.wap.wbxml',
			'wmlc' => 'application/vnd.wap.wmlc',
			'dcr' => 'application/x-director',
			'dir' => 'application/x-director',
			'dxr' => 'application/x-director',
			'dvi' => 'application/x-dvi',
			'gtar' => 'application/x-gtar',
			'php3' => 'application/x-httpd-php',
			'php4' => 'application/x-httpd-php',
			'php' => 'application/x-httpd-php',
			'phtml' => 'application/x-httpd-php',
			'phps' => 'application/x-httpd-php-source',
			'js' => 'application/x-javascript',
			'swf' => 'application/x-shockwave-flash',
			'sit' => 'application/x-stuffit',
			'tar' => 'application/x-tar',
			'tgz' => 'application/x-tar',
			'xht' => 'application/xhtml+xml',
			'xhtml' => 'application/xhtml+xml',
			'zip' => 'application/zip',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'mp2' => 'audio/mpeg',
			'mp3' => 'audio/mpeg',
			'mpga' => 'audio/mpeg',
			'aif' => 'audio/x-aiff',
			'aifc' => 'audio/x-aiff',
			'aiff' => 'audio/x-aiff',
			'ram' => 'audio/x-pn-realaudio',
			'rm' => 'audio/x-pn-realaudio',
			'rpm' => 'audio/x-pn-realaudio-plugin',
			'ra' => 'audio/x-realaudio',
			'wav' => 'audio/x-wav',
			'bmp' => 'image/bmp',
			'gif' => 'image/gif',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'eml' => 'message/rfc822',
			'css' => 'text/css',
			'html' => 'text/html',
			'htm' => 'text/html',
			'shtml' => 'text/html',
			'log' => 'text/plain',
			'text' => 'text/plain',
			'txt' => 'text/plain',
			'rtx' => 'text/richtext',
			'rtf' => 'text/rtf',
			'xml' => 'text/xml',
			'xsl' => 'text/xml',
			'mpeg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mov' => 'video/quicktime',
			'qt' => 'video/quicktime',
			'rv' => 'video/vnd.rn-realvideo',
			'avi' => 'video/x-msvideo',
			'movie' => 'video/x-sgi-movie'
		);
		return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}

}
