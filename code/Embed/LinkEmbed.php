<?php

class LinkEmbed extends FileEmbed
{
    
    public function Image($url, $alt = '')
    {
        $altattr = Convert::raw2att($alt);
        $imageSRC = Convert::raw2att(Director::absoluteURL($url));
        $width = $this->getWidth($url);
        $height = $this->getHeight($url);
        return
            "<img
				border=\"0\"
				src=\"$imageSRC\"
				width=\"$width\"
				height=\"$height\"
				alt=\"$altattr\"
			/>";
    }
}
