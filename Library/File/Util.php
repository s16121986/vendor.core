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

}
