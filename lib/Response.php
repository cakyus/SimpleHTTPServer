<?php

namespace SimpleHTTPServer;

class Response {
	
	// <CONFIGURATION>
	private $httpVersion;
	// </CONFIGURATION>
	
	private $status;
	private $content;
	private $module;
	
	public function __construct(Request $request) {
		
		$config = new Config;
		
		$this->httpVersion = $config->httpVersion;
		$this->status = 200;
		
		// Invalid HTTP Request
		if ($request->isValid() == false) {
			$module = new Module\Error($request);
			$module->setStatus(400); // Bad Request
			$this->module = 'Error';
			$this->status = $module->getStatus();
			$this->content = $module->getContent();
			return true;
		}
		
		// Modules
		
		foreach ($config->modules as $moduleName) {
			
			$className = __NAMESPACE__.'\\Module\\'.$moduleName;
			$module = new $className($request);
			
			if ($module->isValid()) {
				$this->module = $moduleName;
				$this->status = $module->getStatus();
				$this->content = $module->getContent();
				return true;
			}
		}
		
		// Should not goes here. In case it's happens, it will be ..
		// 501 Internal Server Error
		$module = new Module\Error($request);
		$module->setStatus(501); // Internal Server Error
		$this->module = 'Error';
		$this->status = $module->getStatus();
		$this->content = $module->getContent();
		return true;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getContent() {
		return $this->content;
	}
	
    public function getModule() {
		return $this->module;
	}
	
}
