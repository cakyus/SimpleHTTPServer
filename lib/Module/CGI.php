<?php

/**
 * Handle CGI Request
 **/

namespace SimpleHTTPServer\Module;

use SimpleHTTPServer\IModule;
use SimpleHTTPServer\Request;
use SimpleHTTPServer\Module;

class CGI extends Module implements IModule {
	
	public function __construct(Request $request) {
		parent::__construct($request);
		
		$file = $request->getCGIVar('SCRIPT_FILENAME');
		
		if (	is_file($file) == false
			||	preg_match("/\.php$/", $file) == false
			) {
			$this->valid = false;
			return false;
		}
		
		
		$this->status = 200;
		$this->setHeader('Content-Type', 'text/html');
		
		// PHP scripts
		$cmdIO = array(
			  0 => array('pipe', 'r') // stdin
			, 1 => array('pipe', 'w') // stdout
			, 2 => array('file', 'error.log', 'aw') // stderr
			);
		
		// REDIRECT_STATUS is required by php-cgi
		$request->setCGIVar('REDIRECT_STATUS', 200);
		$request->setCGIVar('GATEWAY_INTERFACE', 'CGI/1.1');
		
		$cmdEnv = $request->getCGIVars();
		
		$cmdHadler = proc_open(
			  'php-cgi', $cmdIO, $cmdPipes
			, $request->getCGIVar('DOCUMENT_ROOT'), $cmdEnv
			);
			
		if (is_resource($cmdHadler)) {
			
			if ($request->getCGIVar('REQUEST_METHOD') == 'POST') {
				$postMessage = $request->getPostMessage();
				if (strlen($postMessage)) {
					// POST Method, write POST Message to STDIN
					fwrite($cmdPipes[0], $request->getPostMessage()."\n");
				}
			}
			
			fclose($cmdPipes[0]);
			
			$this->content = stream_get_contents($cmdPipes[1]);
			proc_close($cmdHadler);
		}
	}
	
	public function getContent() {
		return 'HTTP/'.$this->httpVersion
		.' '.$this->status
		.' '.$this->getStatusMessage()."\r\n"
		.$this->content
		;
	}
}


