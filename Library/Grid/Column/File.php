<?php

namespace Grid\Column;

use Api\File\Util as FileUtil;

class File extends AbstractColumn {

	protected $_options = [
		'hrefTarget' => '_blank'
	];

	public function formatValue($value, $row = null) {
		if ($value)
			return '<a href="' . FileUtil::getHttpUrl($value) . '" target="_blank">скачать</a>';
		return '';
	}

}
