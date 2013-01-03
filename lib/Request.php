<?php

namespace SimpleHTTPServer;

class Request {
	
	public $maxRequestSize = 2097152; // 2MB
	
	private $remoteHost;
	private $remotePort;
	private $method;
	private $headerRaw;
	private $uri;
	private $content;
	private $contentRaw;
	private $valid;
	private $documentRoot;
	
	public function __construct($sock) {
		
		$logger = new Logger;
		
		$this->headers = array();
		$this->valid = false;
		$this->documentRoot = dirname(dirname(__FILE__)).'/htdocs';
		
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
			$this->uri = $matches[2];
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
		preg_match("/^[^\?]+/s", $this->uri, $match);
		return $match[0];
	}
	
	public function getQueryString() {
		if (preg_match("/\?([^\s]+)/", $this->uri, $match)) {
			return $match[1];
		}
		return false;
	}
	
	public function getURI() {
		return $this->uri;
	}
	
	public function getHeaderRaw() {
		return $this->headerRaw;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getContentRaw() {
		return $this->contentRaw;
	}
	
	public function getDocumentRoot() {
		return $this->documentRoot;
	}
	
	public function getScriptPath() {
		
		$path = $this->documentRoot.$this->getPath();

		// resolve directory index files
		if (is_dir($path)) {
			if (is_file($path.'index.html')) {
				return $path.'index.html';
			} elseif (is_file($path.'index.php')) {
				return $path.'index.php';
			}
		} elseif (is_file($path)) {
			return $path;
		}
		
		return false;
	}
	
	public function getCGIVars() {
		$vars = array();
		$vars['REQUEST_METHOD'] = $this->getMethod();
		$vars['QUERY_STRING'] = $this->getQueryString();
		$vars['REQUEST_URI'] = $this->getURI();
		$vars['SCRIPT_NAME'] = $this->getPath();
		$vars['REMOTE_ADDR'] = $this->getRemoteHost();
		$vars['REMOTE_PORT'] = $this->getRemotePort();
		$vars['SCRIPT_FILENAME'] = $this->getScriptPath();
		$vars['DOCUMENT_ROOT'] = $this->documentRoot;
		$vars['REDIRECT_STATUS'] = 200;
		$vars['GATEWAY_INTERFACE'] = 'CGI/1.1';
		return $vars;
	}
	
	public function getRemoteHost() {
		return $this->remoteHost;
	}
	
	public function getRemotePort() {
		return $this->remotePort;
	}
}
