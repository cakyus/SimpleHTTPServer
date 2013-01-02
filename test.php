<?php

error_reporting(E_ALL);

require_once('lib/Server.php');
require_once('lib/Logger.php');
require_once('lib/Request.php');
require_once('lib/Response.php');

$server = new \SimpleHTTPServer\Server;
$server->port = '8808';
$server->start();
