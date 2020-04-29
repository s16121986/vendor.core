<?php

namespace File;

abstract class Util {

	public static function getMimeType(AbstractFile $file) {
		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		if ($file->tmp_name)
			return $finfo->file($file->tmp_name);
		elseif ($file->fullname)
			if ($file->exists())
				return $finfo->file($file->fullname);

			elseif ($file->content)
				return $finfo->buffer($file->content);

		return null;
	}

	public static function getSize(AbstractFile $file) {
		return filesize($file->fullname);
	}

}
