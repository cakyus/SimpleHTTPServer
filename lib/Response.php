<?php

namespace SimpleHTTPServer;

class Response {
	
	private $status;
	private $headers;
	private $content;
	
	private $httpVersion = '1.1';
	
	public function __construct(Request $request) {
		
		$this->status = 200;
		$this->headers = array();
		$this->content = 'It\'s Works';
		
		$this->setHeader('Content-Type', 'text/html');
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getMessage() {
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
