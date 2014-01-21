<?php

class FileEmbed extends ViewableData {
	
	public function getWidth($file) {
		$imagefile = realpath(BASE_PATH . '/' . $file);
		if(empty($imagefile)) return null;
		$size = getimagesize($imagefile);
		return $size[0];
	}
	
	public function getHeight($file) {
		$imagefile = realpath(BASE_PATH . '/' . $file);
		if(empty($imagefile)) return null;
		$size = getimagesize($imagefile);
		return $size[1];
	}
}
