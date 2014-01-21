<?php

class SignatureController extends Controller {
	
	private static $allowed_actions = array('Form');
	
	public function Link() {
		return self::config()->urlsegment . '/';
	}
	
	public function index() {
		return $this->renderWith('SignatureForm');
	}
	
	protected function saveDetails($data) {
		
	}
	
	protected function loadDetails($form) {
		
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
				new OptionsetField('Format', 'Output Format *', array('HTML', 'Plain Text', 'Outlook Package'))
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
		


	public function download($data, Form $form) {
		$signature = $this->updateRecord($form);
		
		
		return $this
			->customise($signature)
			->renderWith('Signature');
	}
	
	public function preview($data, $form) {
		$signature = $this->updateRecord($form);
		
		return $this
			->customise($signature)
			->renderWith('Signature');
	}
}
