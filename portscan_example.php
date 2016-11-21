<?php

include_once 'class.PortScan.php';

//if running in web, print "<pre>" first
if ( php_sapi_name() != 'cli' )
	echo "<pre>\n";

	
//scan a single port
$server = '127.0.0.1';
$port = 80;
$result = PortScan::scan($server, $port);
if ($result) 
	echo "port $port is opened";
else
	echo "port $port is not opened";
	
	
echo "\n\n\n";


//scan a range of port, and print process on screen.
$server = '127.0.0.1';
echo "scanning ports of $server ...\n";
$arr = PortScan::scanRange($server, 1, 1024, true);
if ($arr) {
	echo "\n\nscan result:\n";
	print_r($arr);
} else {
	echo "scan error";
}

?>