<?php

/**
 * Handle server generated directory listing
 **/

namespace SimpleHTTPServer\Module;

use SimpleHTTPServer\IModule;
use SimpleHTTPServer\Request;
use SimpleHTTPServer\Module;

class Directory extends Module implements IModule {
	
	public function __construct(Request $request) {
		parent::__construct($request);
		
		$path = $request->getCGIVar('SCRIPT_FILENAME');
		
		// Only directory will be processed
		if (!is_dir($path)) {
			$this->valid = false;
			return false;
		}
		
		if (substr($path, -1) != '/') {
			$path = $request->getCGIVar('SCRIPT_NAME').'/';
			$this->status = '301';
			$this->setHeader('Location', $path);
			$this->content = $this->status
				.' '.$this->getStatusMessage()
				.'<br />Redirecting to '.$path
				;
			//var_dump($this->getContent()); die();
			return true;
		}
				
		$this->status = 501; // Not Implemented
		$this->setHeader('Content-Type', 'text/html');		
		$this->content = $this->status
			.' '.$this->getStatusMessage()
			.'<br />Directory Listing is not implemented'
			;
		
		return true;
	}
}
