<?php

/**
 * Handle index files
 **/

namespace SimpleHTTPServer\Module;

use SimpleHTTPServer\IModule;
use SimpleHTTPServer\Request;
use SimpleHTTPServer\Module;

class Index extends Module implements IModule {
	
	public function __construct(Request $request) {
		parent::__construct($request);
		
		$dir = $request->getCGIVar('SCRIPT_FILENAME');
		$files = array('index.html', 'index.php');
		
		if (is_dir($dir)) {
			foreach ($files as $file) {
				if (is_file($dir.$file)) {
					$request->setCGIVar('SCRIPT_FILENAME', $dir.$file);
					break;
				}
			}
		}
		
		// always return false. coz, the real handler is another module
		$this->valid = false;
	}
}
