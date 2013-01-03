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
	private $postMessage;
	
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
        if (preg_match("/^(GET|POST) ([^\s]+) HTTP\/[0-9]\.[0-9]\r\n(.+?)(\r\n\r\n(.*))?$/s"
			, $this->contentRaw, $match)) {
			$this->method = $match[1];
			$this->uri = $match[2];
			$this->headerRaw = $match[3];
			$this->valid = true;
			if (isset($match[5])) {
				$this->postMessage = $match[5];
			}
			return true;
		}
	}
	
	public function getPostMessage() {
		return $this->postMessage;
	}
	
	public function isValid() {
		return $this->valid;
	}
	
	public function getMethod() {
		return $this->method;
	}
	
	public function getPath() {
		if (preg_match("/^[^\?]+/s", $this->uri, $match)) {
			return $match[0];
		}
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
		
		// Minimum CGI Environments Variables
		$vars['REQUEST_METHOD'] = $this->getMethod();
		$vars['QUERY_STRING'] = $this->getQueryString();
		$vars['REQUEST_URI'] = $this->getURI();
		$vars['SCRIPT_NAME'] = $this->getPath();
		$vars['REMOTE_ADDR'] = $this->getRemoteHost();
		$vars['REMOTE_PORT'] = $this->getRemotePort();
		$vars['SCRIPT_FILENAME'] = $this->getScriptPath();
		$vars['DOCUMENT_ROOT'] = $this->getDocumentRoot();
		$vars['REDIRECT_STATUS'] = 200;
		$vars['GATEWAY_INTERFACE'] = 'CGI/1.1';
		
		// Passthrough client HTTP Headers
		if (preg_match_all("/^([^:]+): (.+)\s$/m", $this->headerRaw, $match)) {
			for ($i = 0; $i < count($match[1]); $i++){
				// eg. User-Agent -> HTTP_USER_AGENT
				$vars['HTTP_'.strtoupper(str_replace('-','_',$match[1][$i]))]
					= $match[2][$i];
			}
		}
		
		// POST Method
		if ($this->getMethod() == 'POST') {
			$vars['CONTENT_LENGTH'] = strlen($this->getPostMessage());
			if (isset($vars['HTTP_CONTENT_TYPE'])) {
				$vars['CONTENT_TYPE'] = $vars['HTTP_CONTENT_TYPE'];
			}
		}
		
		return $vars;
	}
	
	public function getRemoteHost() {
		return $this->remoteHost;
	}
	
	public function getRemotePort() {
		return $this->remotePort;
	}
}
