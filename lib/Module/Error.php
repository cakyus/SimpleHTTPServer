<?php

/**
 * Server generated error page
 **/

namespace SimpleHTTPServer\Module;

use SimpleHTTPServer\IModule;
use SimpleHTTPServer\Request;
use SimpleHTTPServer\Module;

class Error extends Module implements IModule {
	
	private $message;
	
	public function __construct(Request $request) {
		parent::__construct($request);
		$this->setHeader('Content-Type', 'text/html');
	}
	
	public function setStatus($status) {
		$this->status = $status;
	}
	
	public function setMessage($message) {
		$this->message = $message;
	}
	
	public function getContent() {
		
		if ($this->message) {
			$this->message = '<br />'.$this->message;
		}
		
		$this->content = $this->status
			.' '.$this->getStatusMessage()
			.$this->message
			;
			
		return parent::getContent();
	}
}
