<?php

abstract class FileEmbed extends ViewableData {
	
	/**
	 * Generates the necessary XML namespace identifiers required for this document
	 * 
	 * @return string
	 */
	public function getXMLNS() {}
	
	/**
	 * Generates any necessary head tags for this document
	 * 
	 * @return string
	 */
	public function getHead() {}
	
	/**
	 * Gets the width of a file
	 * 
	 * @param string $file Path to file
	 * @return integer
	 */
	public function getWidth($file) {
		$imagefile = realpath(BASE_PATH . '/' . $file);
		if(empty($imagefile)) return null;
		$size = getimagesize($imagefile);
		return $size[0];
	}
	
	/**
	 * Gets the height of a file
	 * 
	 * @param string $file Path to file
	 * @return integer
	 */
	public function getHeight($file) {
		$imagefile = realpath(BASE_PATH . '/' . $file);
		if(empty($imagefile)) return null;
		$size = getimagesize($imagefile);
		return $size[1];
	}
	
	/**
	 * Embed a file
	 * 
	 * @param string $url URL or path to file
	 * @param string $alt alt text for this file
	 * @return string HTML for this image
	 */
	abstract function Image($url, $alt = '');
}
