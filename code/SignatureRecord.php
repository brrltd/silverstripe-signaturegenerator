<?php

class SignatureRecord extends DataObject {
	private static $db = array(
		'Name' => 'Varchar(255)',
		'Position' => 'Varchar(255)',
		'DirectDial' => 'Varchar(20)',
		'Mobile' => 'Varchar(20)',
		'Email' => 'Varchar(255)',
		'Reference' => 'Varchar(255)'
	);
	
	protected function onBeforeWrite() {
		parent::onBeforeWrite();
		
		if(empty($this->Reference)) {
			$this->Reference = uniqid();
		}
	}
}
