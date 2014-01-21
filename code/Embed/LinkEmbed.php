<?php

class LinkEmbed extends FileEmbed {
	public function Image($url) {
		return
			'<img
				src="'.Convert::raw2att(Director::absoluteURL($url)).'"
				width="'.$this->getWidth($url).'"
				height="'.$this->getHeight($url).'"
			/>';
	}
}
