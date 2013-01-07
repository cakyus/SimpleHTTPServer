<?php

/**
 * Handle static files
 **/

namespace SimpleHTTPServer\Module;

use SimpleHTTPServer\IModule;
use SimpleHTTPServer\Request;
use SimpleHTTPServer\Module;

class File extends Module implements IModule {
	
	public function __construct(Request $request) {
		parent::__construct($request);
		
		$file = $request->getCGIVar('SCRIPT_FILENAME');
		
		if (!is_file($file)) {
			$this->status = 404;
			$this->setHeader('Content-Type', 'text/html');
			$this->content = $this->status.' '.$this->getStatusMessage()
				.'<br />'.htmlentities($request->getCGIVar('SCRIPT_NAME'))
				;
			return false;
		}
		
		$this->status = 200;
		$this->setHeader('Content-Type', $request->getMimeContentType());
		$this->content = file_get_contents($file);
	}
}
