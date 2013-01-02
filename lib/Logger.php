<?php

namespace SimpleHTTPServer;

class Logger {
	
	public function write() {
		echo date('Y-m-d H:i:s ').implode(' ', func_get_args())."\n";
	}
}
