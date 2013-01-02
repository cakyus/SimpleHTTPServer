<?php

namespace SimpleHTTPServer;

class Server {
	
	public $host = '127.0.0.1';
	public $port = 8808;
	
	public function start() {
		
		$logger = new Logger;
		
		$logger->write('Serving at '.$this->host.' port '.$this->port);
		
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
		
		if (socket_bind($sock, $this->host, $this->port) === false) {
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
			
			$request = new Request($clientSock);
			$response = new Response($request);
			$message = $response->getMessage();
			$messageSize = strlen($message);
			
			$logger->write(
				  $request->getRemoteHost()
				, $request->getRemotePort()
				, $request->getMethod()
				, $request->getURI()
				, $response->getStatus()
				, $messageSize
				);
			
			socket_write($clientSock, $message, $messageSize);
			socket_close($clientSock);
			
			// @todo find the way to shutdown server with Ctrl+C
			//       in which immidiately release binding 
			//       to listened port
			if ($request->getURI() == '/stop') {
				break;
			}
		};
    
		socket_close($sock);
		
		$logger->write('Shutdown');
	}
}

