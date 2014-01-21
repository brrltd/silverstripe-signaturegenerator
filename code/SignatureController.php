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
					'Outlook' => 'Outlook Package'
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
		if($format === 'html') {
			$this->response->addHeader('Content-Type', 'text/html; charset=UTF-8');
		} else {
			$this->response->addHeader('Content-Type', 'text/plain; charset=UTF-8');
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

	public function download($data, Form $form) {
		$signature = $this->updateRecord($form);
		$format = $this->getFormat($data);
		$filename = $this->generateFilename($data['Name'], $format);
		
		// Specify download
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
