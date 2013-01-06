<?php

namespace SimpleHTTPServer;

class Server {

	// <CONFIGURATION>
	private $serverName;
	private $serverPort;
	// </CONFIGURATION>
	
	public function start() {
		
		$config = new Config;
		$logger = new Logger;
		
		$this->serverName = $config->serverName;
		$this->serverPort = $config->serverPort;
		
		$logger->write('Serving at '.$this->serverName.' port '.$this->serverPort);
		
		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			$logger->write('socket_create() failed. '.socket_strerror(socket_last_error()));
			return false;
		}
		
		// Reuse address and port, and get rid of error: 
		// "unable to bind, address already in use"
		// http://php.net/manual/en/function.socket-bind.php#22200
		
		if (!socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1)) {
			$logger->write(socket_strerror(socket_last_error($sock)));
			return false;
		} 
		
		if (socket_bind($sock, $this->serverName, $this->serverPort) === false) {
			$logger->write('socket_bind() failed. '.socket_strerror(socket_last_error($sock)));
			return false;
		}

		if (socket_listen($sock, 5) === false) {
			$logger->write('socket_listen() failed. '.socket_strerror(socket_last_error($sock)));
			return false;
		}
		
		while (true) {
			
			if (($clientSock = socket_accept($sock)) === false) {
				$logger->write('socket_accept() failed '.socket_strerror(socket_last_error($sock)));
				continue;
			}
			
			$remoteHost = '';
			$remotePort = '';
			
			$request = new Request($clientSock, $this);
			$response = new Response($request);
			$message = $response->getMessage();
			$messageSize = strlen($message);
			
			$logger->write(
				  $request->getCGIVar('REMOTE_ADDR')
				, $request->getCGIVar('REMOTE_PORT')
				, $request->getCGIVar('REQUEST_METHOD')
				, $request->getCGIVar('SCRIPT_NAME')
				, $response->getStatus()
				, $messageSize
				);
			
			socket_write($clientSock, $message, $messageSize);
			socket_close($clientSock);
		};
    
		socket_close($sock);
		
		$logger->write('Shutdown');
	}
	
	public function getServerName() {
		return $this->serverName;
	}
	
	public function getServerPort() {
		return $this->serverPort;
	}
}

