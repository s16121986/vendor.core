<?php
use Http\Content\Factory as ContentFactory;

class Watermark {
	
	public static $image = '';
	public static $images = array();
	public static $minSize = false;

	public static function output($image) {
		//Output::factory()->output($image);
		//return;
		$out = ContentFactory::get('Image', $image);
		//$out->enableCache($image->fullname);
		//$fileModTime = Output::outputHeaders($image->fullname);
		ob_start();
		try {
			switch ($image->mime_type) {
				case 'image/jpg':
				case 'image/jpeg':
					$imm = imagecreatefromjpeg($image->fullname);
					break;
				case 'image/png':
					$imm = imagecreatefrompng($image->fullname);
					break;
				case 'image/gif':
					$imm = imagecreatefromgif($image->fullname);
					break;
			}
		} catch (Exception $e) {
			$imm = false;
		}
		if (!$imm) {
			return false;
		}
		//$color = imagecolorallocate($imm, 255, 255, 255);
		$so = array(imagesx($imm), imagesy($imm));
		if (!self::$minSize || ($so[0] > self::$minSize && $so[1] > self::$minSize)) {
			switch (true) { //watermark size
				case $so[0] <= 400: $s = 20; break;
				case $so[0] <= 600: $s = 40; break;
				default: $s = 80; break;
			}
			$wi = imagecreatefrompng(self::$images[$s]);
			imagecopy($imm, $wi, $so[0] - $s - 20, $so[1] - $s - 20, 0, 0, $s, $s);
		}
		/* $wx = round(imagesx($wi) / 2);
		  $wy = round(imagesy($wi) / 2);
		  $sx = imagesx($imm);
		  $xc = floor($sx / 500);
		  if ($xc < 1) $xc = 1;
		  $sy = imagesy($imm);
		  $yc = floor($sy / 400);
		  if ($yc < 1) $yc = 1;
		  $px = round($sx / ($xc + 1));
		  $py = round($sy / ($yc + 1));
		  for ($i = 1; $i <= $xc; $i ++) {
		  for ($j = 1; $j <= $yc; $j ++) {
		  $x = $px * $i - $wx;
		  $y = $py * $j - $wy;
		  imagecopy($imm, $wi, $x, $y, 0, 0, 143, 145);
		  }
		  } */
		imagejpeg($imm, null, 100);
		imagedestroy($imm);
		
		$imageData = ob_get_contents();
		//$ImageDataLength = ob_get_length();
		//$out->setContent(ob_get_contents());
		//$out->set('max-age', 29030400);
		$out->setContentType($image->mime_type);
		$out->setContentLength(ob_get_length());
		$out->setHeader('Content-Disposition', 'inline; filename="' . $image->name . '"');
		ob_end_clean();
		$out->sendHeaders();
		echo $imageData;
		exit;
	}

}
