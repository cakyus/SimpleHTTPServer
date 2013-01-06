<?php

namespace SimpleHTTPServer;

class Config {
	
	public $serverName = '127.0.0.1';
	public $serverPort = 8808;
	
	// Maximum number of bytes which client can send to server
	public $maxRequestSize = 2097152; // 2 MB
	// Location of the folder in which the files will be served
	public $documentRoot;
	// Default MIME Content-Type
	public $mimeContentType = 'text/html';
	
	// HTTP Version which server supported
	public $httpVersion = '1.1';
	
	public function __construct() {
		$this->documentRoot = dirname(dirname(__FILE__)).'/htdocs';
	}
	
}
