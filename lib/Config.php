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
	
	// Response modules
	public $modules;
	
	// Location of log file path
	public $logPath;
	
	// Location of pid file path
	public $pidPath;
	
	// Location of php-cgi path
	public $phpCGIPath;
	
	public function __construct() {
		$this->documentRoot = dirname(dirname(__FILE__)).'/htdocs';
		$this->modules = array(	'Index', 'Directory', 'CGI', 'File');
		$this->logPath = sys_get_temp_dir().'/SimpleHTTPServer.log';
		$this->pidPath = sys_get_temp_dir().'/SimpleHTTPServer.pid';
		
		// locate php-cgi
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			//windows
			$phpExePath = exec('where php.exe');
			$phpDir = dirname($phpExePath);
			$this->phpCGIPath = $phpDir.'/php-cgi.exe';
		} else {
			// Linux ?
			$this->phpCGIPath = 'php-cgi';
		}
	}
	
}
