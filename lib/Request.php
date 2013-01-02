<?php

namespace SimpleHTTPServer;

class Request {
	
	public $maxRequestSize = 2097152; // 2MB
	
	private $remoteHost;
	private $remotePort;
	private $method;
	private $headerRaw;
	private $path;
	private $content;
	private $contentRaw;
	private $valid;
	
	public function __construct($sock) {
		
		$logger = new Logger;
		
		$this->headers = array();
		$this->valid = false;
		
		socket_getpeername(
			  $sock
			, $this->remoteHost
			, $this->remotePort
			);
			
        if (false === ($this->contentRaw = socket_read($sock, $this->maxRequestSize))) {
            $logger->write('socket_read() failed'
				, socket_strerror(socket_last_error($sock))
				);
			return false;
        }
        
        if (!$this->contentRaw = trim($this->contentRaw)) {
			return false;
        }
        
        // @todo POST Request will requires advanced regex pattern
        //       for request body
        if (preg_match("/^(GET|POST) ([^\s]+) HTTP\/[0-9]\.[0-9]\r\n(.*)$/s"
			, $this->contentRaw, $matches)) {
			$this->method = $matches[1];
			$this->path = $matches[2];
			$this->headerRaw = $matches[3];
			$this->valid = true;
			return true;
		}
		
	}
	
	public function isValid() {
		return $this->valid;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getContentRaw() {
		return $this->contentRaw;
	}
	
	public function getRemoteHost() {
		return $this->remoteHost;
	}
	
	public function getRemotePort() {
		return $this->remotePort;
	}
}
