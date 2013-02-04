<?php 

session_start();

echo '<center><pre>';
echo 'Session-Id: '.session_id()."\n";
if (!isset($_SESSION['Hit-Count'])) {
	$_SESSION['Hit-Count'] = 0;
}
$_SESSION['Hit-Count']++;
echo 'Hit-Count: '.$_SESSION['Hit-Count']."\n";
echo '</pre></center>';

phpinfo(32);

