<?php

namespace SimpleHTTPServer;

class Response {
	
	private $status;
	private $headers;
	private $content;
	
	private $httpVersion = '1.1';
	private $isCGIRequest = false;
	
	public function __construct(Request $request) {
		
		$this->status = 200;
		$this->headers = array();
		
		$this->setHeader('Content-Type', 'text/html');
		
		if ($request->isValid()) {
			
			$path = $request->getScriptPath();
			
			if (is_dir($path)) {
				// Directory list is not supported
				$this->status = 501; // Not Implemented
				$this->content = $this->status
					.' '.$this->getStatusMessage()
					.'<br />Directory Listing is not implemented'
					;
				return false;
			} elseif (!is_file($path)) {
				// 404 Not Found
				$this->status = 404;
				$this->content = $this->status
					.' '.$this->getStatusMessage()
					.'<br />'.htmlentities($request->getPath())
					;
				return false;
			} elseif (preg_match("/\.php$/", $path)) {
				// PHP scripts
				$cmdIO = array(
					  0 => array('pipe', 'r') // stdin
					, 1 => array('pipe', 'w') // stdout
					, 2 => array('file', 'error.log', 'aw') // stderr
					);
				$cmdEnv = $request->getCGIVars();
				
				$cmdHadler = proc_open(
					  'php-cgi', $cmdIO, $cmdPipes
					, $request->getDocumentRoot(), $cmdEnv
					);
					
				if (is_resource($cmdHadler)) {
					
					if ($request->getMethod() == 'POST') {
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
				
				$this->isCGIRequest = true;
				
			} else {
				// Static files
				$this->setHeader('Content-Type',mime_content_type($path));
				$this->content = file_get_contents($path);
			}
			
		} else {
			$this->status = 400; // Bad Request
			$this->content = $this->status.' '.$this->getStatusMessage();
		}
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getMessage() {
		
		if ($this->isCGIRequest) {
			return 'HTTP/'.$this->httpVersion
			.' '.$this->status
			.' '.$this->getStatusMessage()."\r\n"
			.$this->content
			;
		}
		
		return 'HTTP/'.$this->httpVersion
			.' '.$this->status
			.' '.$this->getStatusMessage()."\r\n"
			.implode("\r\n", $this->headers)."\r\n"
			."\r\n".$this->content
			;
	}
	
	public function setHeader($name, $value) {
		$this->headers[$name] = $name.': '.$value;
	}
	
	public function getStatusMessage() {
		$messages = array(
			100 => "Continue",
			101 => "Switching Protocols",
			200 => "OK",
			201 => "Created",
			202 => "Accepted",
			203 => "Non-Authoritative Information",
			204 => "No Content",
			205 => "Reset Content",
			206 => "Partial Content",
			300 => "Multiple Choices",
			301 => "Moved Permanently",
			302 => "Found",
			303 => "See Other",
			304 => "Not Modified",
			305 => "Use Proxy",
			307 => "Temporary Redirect",
			400 => "Bad Request",
			401 => "Unauthorized",
			402 => "Payment Required",
			403 => "Forbidden",
			404 => "Not Found",
			405 => "Method Not Allowed",
			406 => "Not Acceptable",
			407 => "Proxy Authentication Required",
			408 => "Request Timeout",
			409 => "Conflict",
			410 => "Gone",
			411 => "Length Required",
			412 => "Precondition Failed",
			413 => "Request Entity Too Large",
			414 => "Request-URI Too Long",
			415 => "Unsupported Media Type",
			416 => "Requested Range Not Satisfiable",
			417 => "Expectation Failed",
			500 => "Internal Server Error",
			501 => "Not Implemented",
			502 => "Bad Gateway",
			503 => "Service Unavailable",
			504 => "Gateway Timeout",
			505 => "HTTP Version Not Supported",
		);
		
		if (isset( $messages[$this->status])) {
			return $messages[$this->status];
		}
		
		throw new \Exception('Unkown status code. "'.$this->status.'"');
    }
}
