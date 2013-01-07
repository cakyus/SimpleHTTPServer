<?php

error_reporting(E_ALL);

// Main files
require_once('lib/Config.php');
require_once('lib/Server.php');
require_once('lib/Logger.php');
require_once('lib/Request.php');
require_once('lib/Response.php');

// Response modules
require_once('lib/Response/CGI.php');
require_once('lib/Response/Directory.php');
require_once('lib/Response/Error.php');
require_once('lib/Response/File.php');
require_once('lib/Response/Index.php');

// By default, Server will run at 127.0.0.1:8808
// To change this behaviour, you need to look at lib/Config.php

$server = new \SimpleHTTPServer\Server;
$server->start();
