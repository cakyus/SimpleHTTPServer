<?php

namespace SimpleHTTPServer;

/**
 * Interface for module
 **/

interface IModule {
	
	/**
	 * Provide access to Response and Request object
	 **/
	
	public function __construct(Request $request);
	
	/**
	 * Indicate that the module can handle request
	 * 
	 * @return boolean
	 **/
	
	public function isValid();
		
	/**
	 * Get HTTP response
	 * 
	 * @return string
	 **/
	public function getContent();
}
