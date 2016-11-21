<?php

include_once 'class.Ping.php';
include_once 'class.PortScan.php';

//if running in web, print "<pre>" first
if ( php_sapi_name() != 'cli' )
	echo "<pre>\n";


//ping a host
$host = 'www.bing.com';
$result = Ping::test($host);
if ($result) 
	echo "ping $host in $result ms\n";
else
	echo "can not ping $host\n";

//flush output
@ob_flush(); flush(); 


//try ping a host 3 times and get statistics
$arr = Ping::fullTest($host, 3);
if ($arr) 
	print_r($arr);
else
	echo "can not pint $host\n";

//flush output
@ob_flush(); flush();


//scan IPs :  from 192.168.0.1 to 192.168.0.20
$arr = Ping::scan('192.168.0.1', '192.168.0.20', 1000, true);
if ( $arr ) 
	print_r($arr);
else
	echo "error scan IPs\n";


?>