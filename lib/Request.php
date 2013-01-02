<?php

namespace SimpleHTTPServer;

class Request {
	
	public $maxRequestSize = 2097152; // 2MB
	
	private $remoteHost;
	private $remotePort;
	private $method;
	private $headers;
	private $path;
	private $content;
	private $contentRaw;
	
	public function __construct($sock) {
		
		$logger = new Logger;
		
		$this->headers = array();
		
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
