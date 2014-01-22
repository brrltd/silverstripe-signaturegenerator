<?php

class SignatureController extends Controller {
	
	private static $allowed_actions = array('Form');
	
	public function Link() {
		return self::config()->urlsegment . '/';
	}
	
	public function index() {
		return $this->renderWith('SignatureForm');
	}
	
	/**
	 * Gets a persistant signature record
	 * 
	 * @param boolean $create Should a record be created if it doesn't exist?
	 * @return SignatureRecord
	 */
	protected function getSignatureRecord($create = false) {
		$reference = Cookie::get('SignatureReference');
		if($reference) {
			$record = SignatureRecord::get()
				->filter('Reference', $reference)
				->first();
			if($record) return $record;
		}
		
		if(!$create) return null;
		
		$record = SignatureRecord::create();
		$record->write();
		Cookie::set('SignatureReference', $record->Reference);
		return $record;
	}
	
	public function Form() {
		
		$record = $this->getSignatureRecord();
		
		$fields = new FieldList(array(
			new FieldGroup('Personal Details', array(
				new TextField('Name', 'Name *', null, 255),
				new TextField('Position', 'Position', null, 255)
			)),
			new FieldGroup('Contact Details', array(
				TextField::create('DirectDial', 'DDI', null, 20)
					->setAttribute('placeholder', '+64 9 000 0000'),
				TextField::create('Mobile', 'Mobile', null, 20)
					->setAttribute('placeholder', '+64 21 000 0000'),
				new EmailField('Email', 'Email *', null, 255)
			)),
			new FieldGroup('Options', array(
				new OptionsetField('Format', 'Output Format *', array(
					'html' => 'HTML',
					'txt' => 'Plain Text',
					'outlook' => 'Outlook Package'
				),
				'html')
			))
		));
		
		$validator = new RequiredFields('Name', 'Email', 'Format');
		
		$actions = new FieldList(
			new FormAction('preview', 'Preview'),
			new FormAction('download', 'Download')
		);
		
		$form = new Form($this, 'Form', $fields, $actions, $validator);
		if($record) $form->loadDataFrom($record);
		return $form;
	}
	
	/**
	 * 
	 * @param Form $form
	 * @return SignatureRecord
	 */
	protected function updateRecord(Form $form) {
		$signature = $this->getSignatureRecord(true);
		$form->saveInto($signature);
		$signature->write();
		return $signature;
	}
	
	protected function generateFilename($name, $extension = null) {
		$tokens = array_map(function($text) {
			return ucfirst($text);
		}, preg_split('/\W+/', trim($name)));
		
		$name = implode('_', $tokens);
		if($extension) $name .= '.' . $extension;
		return $name;
	}
	
	public function generateTemplate($signature, $embed, $format = 'html') {
		$template = $format === 'html' ? 'SignatureHTML' : 'SignaturePlain';
		return $this
			->customise($signature)
			->customise(array('Embed' => $embed))
			->renderWith($template);
	}
	
	public function setContentType($format = 'html') {
		switch($format) {
			case 'html':
				$this->response->addHeader('Content-Type', 'text/html; charset=UTF-8');
				break;
			case 'txt':
				$this->response->addHeader('Content-Type', 'text/plain; charset=UTF-8');
				break;
			case 'zip':
				$this->response->addHeader('Content-Type', 'application/zip');
				break;
		}
	}
	
	public function setDownload($filename) {
		$this->response->addHeader('Content-Disposition', 'attachment; filename=' . $filename);
	}
	
	protected function getFormat($data) {
		return in_array($data['Format'], array('html', 'txt'))
			? $data['Format']
			: 'html';
	}
	
	/**
	 * Package a signature record into an outlook archive
	 * 
	 * @param SignatureRecord $signature
	 * @return string Binary string representing output content
	 */
	protected function packageOutlook(SignatureRecord $signature) {
		
		// Determine filenames
		$zipFilename = $this->generateFilename($signature->Name, 'zip');
		$folderName = $this->generateFilename($signature->Name).'_files';
		$htmlFilename = $this->generateFilename($signature->Name, 'htm');
		$txtFilename = $this->generateFilename($signature->Name, 'txt');
		
		// Generate content
		$embeddedImages = new VMLEmbed($folderName, $htmlFilename);
		$htmlContent = $this->generateTemplate($signature, $embeddedImages, 'html');
		$txtContent = $this->generateTemplate($signature, null, 'txt');
		$filelistXMLContent = $embeddedImages->getFilelistXML();
		
		// Initialise archive file
		$tempFile = tempnam(sys_get_temp_dir(), uniqid());
		$zip = new ZipArchive();
		if($zip->open($tempFile, ZipArchive::CREATE) !== true) {
			throw new Exception("Could not create zip file at $tempFile");
		}
		
		// Add template files
		$zip->addFromString($htmlFilename, $htmlContent);
		$zip->addFromString($txtFilename, $txtContent);
		
		// Add images
		$zip->addFromString("$folderName/filelist.xml", $filelistXMLContent);
		foreach($embeddedImages->getFiles() as $filename => $location) {
			$filePath = realpath(BASE_PATH . '/' . $location);
			$zip->addFile($filePath, "$folderName/$filename");
		}
		$zip->close();
		
		// Stream zip file content
		$result = file_get_contents($tempFile);
		unlink($tempFile);
		$this->setDownload($zipFilename);
		$this->setContentType('zip');
		return $result;
	}

	public function download($data, Form $form) {
		$signature = $this->updateRecord($form);
		$format = $this->getFormat($data);
		
		// Check if we should package an outlook signature
		if($data['Format'] === 'outlook') {
			return $this->packageOutlook($signature);
		}
		
		// Specify download
		$filename = $this->generateFilename($data['Name'], $format);
		$this->setDownload($filename);
		$this->setContentType($format);
		return $this->generateTemplate($signature, new LinkEmbed(), $format);
	}
	
	public function preview($data, $form) {
		$signature = $this->updateRecord($form);
		$format = $this->getFormat($data);
		
		// Present data
		$this->setContentType($format);
		return $this->generateTemplate($signature, new LinkEmbed(), $format);
	}
}
