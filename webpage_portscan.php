<?php 

include_once 'class.PortScan.php';

$server = "";
$port_start = 1;
$port_end = 1024;


if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['server']) && !empty($_POST['server'])) {
	set_time_limit(0) ;
	
	$server = $_POST['server']; 
	$port_start = $_POST['start']; 
	$port_end = $_POST['end']; 
	if ($port_start<1) $port_start = 1;
	
	echo "server = $server<br>\n"; 
	echo "scan TCP port from $port_start to $port_end<br>\n<pre>"; 
	
	$result = PortScan::scanRange($server, $port_start, $port_end);
	
	echo "\n\n<br>scan finished. ";
	echo empty($result) ?  "no port opened\n" : "find ".count($result)." port opened\n\n\n";
	if ( !empty($result) ) {
		print_r($result);
	}
	echo "</pre>";
}

?>
<center>
<br><br><br>
<h3> Scan port </h3>
<form action="" method="POST">
    <table>
	<tr><td>Server         </td>  <td><input type="text" name="server" value="<?php echo $server;?>"></td></tr>
	<tr><td>scan port from </td>  <td><input type="text" name="start" value="<?php echo $port_start;?>"></td></tr>
	<tr><td>scan port to   </td>  <td><input type="text" name="end" value="<?php echo $port_end;?>"></td></tr>
	<tr><td>&nbsp;  </td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="submit"></td></tr>
	</table>
</form>
</center>