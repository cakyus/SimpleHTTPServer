<?php

error_reporting(E_ALL);

// Main files
require_once('lib/Config.php');
require_once('lib/Server.php');
require_once('lib/Logger.php');
require_once('lib/Request.php');
require_once('lib/Response.php');
require_once('lib/Module.php');

// Interfaces
require_once('lib/IModule.php');

// Response modules
require_once('lib/Module/CGI.php');
require_once('lib/Module/Directory.php');
require_once('lib/Module/Error.php');
require_once('lib/Module/File.php');
require_once('lib/Module/Index.php');

// By default, Server will run at 127.0.0.1:8808
// To change this behaviour, you need to look at lib/Config.php

$server = new \SimpleHTTPServer\Server;
$server->start();
