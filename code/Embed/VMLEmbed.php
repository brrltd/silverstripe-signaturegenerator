<?php

class VMLEmbed extends FileEmbed {
	
	public function getXMLNS() {
		return 
			'xmlns:v="urn:schemas-microsoft-com:vml"
			 xmlns:o="urn:schemas-microsoft-com:office:office"';
	}
	
	public function getHead() {
		return "
			<link rel=File-List href=\"{$this->foldername}/filelist.xml\">
			<style>
			v\:* {behavior:url(#default#VML);}
			o\:* {behavior:url(#default#VML);}
			</style>";
	}
	
	/**
	 * Folder name to place files during embedding
	 *
	 * @var string
	 */
	protected $foldername;
	
	/**
	 * Name of main htm file
	 *
	 * @var string
	 */
	protected $mainFilename;
	
	/**
	 * Generate a XML embedding utility with a reference to a virtual folder name
	 * 
	 * @param string $foldername The folder to use for linked files
	 * @param string $mainFilename Name of main htm file
	 */
	public function __construct($foldername, $mainFilename) {
		parent::__construct();
		
		$this->foldername = $foldername;
		$this->mainFilename = $mainFilename;
		$this->files = array();
	}
	
	/**
	 * Record a file by relative path for later use
	 * 
	 * @param string $url
	 * @return string filename to use in the file
	 */
	protected function useFile($url) {
		$filename = basename($url);
		
		// Check if file already exists
		if(isset($this->files[$filename]) && $this->files[$filename] !== $url) {
			throw new Exception('Duplicate image filename detected in multiple locations: "'
				. $url . '" and "' . $this->files[$filename] . '"');
		}
		
		// Save and return file
		$this->files[$filename] = $url;
		return $filename;
	}
	
	/**
	 * Get list of all filenames used in this template, and their physical locations
	 * 
	 * @return array
	 */
	public function getFiles() {
		return $this->files;
	}
	
	/**
	 * Generate content for filelist.xml embedded file
	 * 
	 * @return string
	 */
	public function getFilelistXML() {
		$content  = '<xml xmlns:o="urn:schemas-microsoft-com:office:office">';
		$content .= "\r\n  <o:MainFile HRef=\"../{$this->mainFilename}\" />";
		foreach($this->getFiles() as $file => $path) {
			$content .= "\r\n  <o:File HRef=\"{$file}\" />";
		}
		$content .= "\r\n</xml>";
		return $content;
	}
	
	
	public function Image($url, $alt = '') {
		$id = uniqid();
		$altattr = Convert::raw2att($alt);
		$width = $this->getWidth($url);
		$height = $this->getHeight($url);
		$filename = $this->useFile($url);
		$localPath = $this->foldername . '/' . $filename;
		$absPath = Director::absoluteURL($url);
		return
			"<!--[if gte vml 1]>
				<v:shape id=\"{$id}\" type=\"#_x0000_t75\" alt=\"{$altattr}\" style='width:{$width};height:{$height}'>
					<v:imagedata src=\"{$localPath}\" o:href=\"{$absPath}\" />
				</v:shape>
			<![endif]-->
			<![if !vml]>
				<img border=0 width=\"{$width}\" height=\"{$height}\" src=\"{$localPath}\" alt=\"{$altattr}\" v:shapes=\"{$id}\">
			<![endif]>";
	}
}
