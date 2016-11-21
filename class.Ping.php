<?php

/**
 * Ping ( a class to perform ping in PHP )
 * 
 * @author JoStudio
 * @version 1.0.1
 * 
 * @example <pre>
   //ping a host
   $result = Ping::test($host);
   if ($result) echo $result;
   
   //try ping 3 times and get statistics
   $arr = Ping::fullTest($host, 3);
   if ($arr) print_r($arr);
   
   
   //scan IPs 
   $arr = Ping::scan('192.168.0.1', '192.168.0.20');
   if ($arr) print_r($arr);
   
   </pre>
 */
class Ping {
	
	/** whether socket_create (SOCK_RAW) is permitted */
	private static $can_SOCK_RAW = null;
	
	/** OS name */
	private static $os = null;
	
	/**
	 * Ping host
	 * @param string $host     host name of IP
	 * @param int    $times    (optional) how many times does it try, default is 1
	 * @param int    $timeout  (optional) timeout in milliseconds, default is 2000 ms
	 * @return int|bool, return average milliseconds. return false if host can not be pinged
	 */
	public static function test($host, $times = 1, $timeout = 2000) {
		$arr = Ping::fullTest($host, 1, $timeout);
		if ($arr && $arr['reached'] > 0) {
			return $arr['average'];
		}
		return false;
	}
	
	/**
	 * Scan IPs (enum the IP, and ping the IP)
	 * @param string $ipStart IP address string, such as '192.168.1.1';
	 * @param string $ipEnd   IP address string, such as '192.168.1.255';
	 * @param int    $timeout  (optional) timeout in milliseconds, default is 200 ms
	 * @param bool   $printProcess (optional)whether print information while processing
	 * 
	 * @return array|bool, return array of Ip which can be pinged. return false if fail.
	 * <pre>
	 *    format of return array:  
	 *    array ( 
	 *       ip => time, //ping time in millisecond
	 *       ...
	 *    ); 
	 * </pre>
	 * @example Ping::scan('192.168.0.1', '192.168.0.255');
	 */
	public static function scan($ipStart, $ipEnd, $timeout = 200, $printProcess = false) {
		//validate parameters
		$ip1 = Ping::ipStringToInt($ipStart);
		$ip2 = Ping::ipStringToInt($ipEnd);
		if ($ip1===false || $ip2===false) return false;
		if ($ip1 > $ip2) return false;
		
		if ( is_bool($timeout) ) {
			$printProcess = $timeout;
			$timeout = 200;
		}
		
		$result = array();
		$ip = $ip1;
		while ($ip <= $ip2) {
			$ipString = Ping::ipIntToString($ip);
			$ret = Ping::test($ipString, 1, $timeout);
			if ($ret) {
				$result[$ipString] = $ret;
				if ($printProcess) Ping::println("$ipString reached in $ret ms");
			} else {
				if ($printProcess) Ping::println("$ipString unreachable or timeout");
			}
			$ip++;
		}
		return $result;
	}
	
	/**
	 * Ping host (full test and statistics)
	 * 
	 * @param string $host     host name of IP
	 * @param int    $times    (optional) how many times does it try, default is 3
	 * @param int    $timeout  (optional) timeout in milliseconds, default is 2000 ms
	 * @return array|bool, return array of statistics. return false if fail.
	 * <pre>
	 *   result array format : 
	 *   array(
	    		'action'  => 'ping', //always 'ping'
	    		'host'    => 'xxx',  //host name
	    		'times'   => n,      //times of ping
	    		'reached' => n,      //times of reached ping. if it is 0, means ping failed
	    		'average' => n,      //average time if reached (in millisecond)
	    		'max'     => n,      //max time if reached (in millisecond)
	    		'min'     => n,      //min time if reached (in millisecond)
	    	);
	 */
	public static function fullTest($host, $times = 3, $timeout = 2000) {
		//if socket is avaliable
		if ( Ping::canPerformSocket() ) {
			//using socket to ping
			$ret = Ping::fullTest_Socket($host, $times, $timeout);
			
			//if fail using command line to ping
			if ($ret === false)
				$ret = Ping::fullTest_CommandLine($host, $times, $timeout);
			
			return $ret;
		} else {
			//using command line to ping
			return Ping::fullTest_CommandLine($host, $times, $timeout);
		}
	}
	
	//full test using socket
	private static function fullTest_Socket($host, $times = 3, $timeout = 2000) {
	    // ICMP ping packet with a pre-calculated checksum
	    $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
	    
	    //validate params
	    $host = trim($host);
	    if (empty($host)) return false;
	    
	    if ( !is_numeric($times) ) $times = 1;
	    $times = intval($times);
	    if ( $times <= 0) $times = 1;
	    
	    if ( !is_numeric($timeout) ) $timeout = 2000;
	    
	    //init statistics variables
	    $total_time = 0;    //accumulated time
	    $times_tried = 0;   //how many times tried
	    $times_reached = 0; //how many times does the host is reached
	    $time_max = 0; //max reached time
	    $time_min = -1; //min reached time
	    
	    //try many times 
	    for ($i = 0; $i < $times; $i++) {
		    //create socket RAW
		    $socket  = socket_create(AF_INET, SOCK_RAW, 1);
		    if ($socket===false) continue;
		    
		    //set option
		    $param = array('sec' => intval($timeout / 1000), 'usec' => ($timeout % 1000) * 1000 );
		    $result = socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $param);
		    if ($result===false) continue;
		    
		    //connect
		    $result = socket_connect($socket, $host, null);
		    if ($result===false) continue;
		    
		    //send package
		    $time = microtime(true); //get current time
		    $result = socket_send($socket, $package, strLen($package), 0);
		    if ($result===false) continue;
		    
		    $times_tried++;
		    
		    //read response
		    if (@socket_read($socket, 255)) {
		    	//calculation time
		        $time = microtime(true) - $time;
		        //update statistics
		        $times_reached++;
		        $total_time += $time;
		        $time_max = ($time > $time_max) ? $time : $time_max;
		        if ($time_min < 0)
		        	$time_min = $time;
		        else
		        	$time_min = ($time < $time_min) ? $time : $time_min;
		    }
		    
		    socket_close($socket);
	    }
	    
	    if ($times_tried == 0) {
	    	return false;
	    	
	    } else {
	    	if ($time_min<0) $time_min = 0;
	    	return array(
	    		'action'  => 'ping',
	    		'host'    => $host,
	    		'times'   => $times_tried,
	    		'reached' => $times_reached,
	    		'average' => $times_reached > 0 ? intval(round($total_time * 1000 / $times_reached)) : 0 ,
	    		'max'     => intval( round($time_max * 1000) ),
	    		'min'     => intval( round($time_min * 1000) ),
	    	);
	    }
	}
	
	//full test using command line
	private static function fullTest_CommandLine($host, $times = 3, $timeout = 2000) {
		//construct ping command line
		switch (Ping::whichOs()) {
			case 'Windows':
				$command = "ping -n $times $host";
				break;
			case 'Linux':
				if ($timeout<1000) $timeout = 1000;
				$command = "ping -c $times -W 1 $host";
				break;
			case 'Mac':
				$command = "ping -c $times -t $times $host";
				break;
			default:
				$command = "ping -c $times -W 1 $host";
				break;
		}
		$output = null;
		$return_var = 0;
	
		//excute ping command line
		exec($command, $output, $return_var);
		if ($return_var != 0) return false;
		if ( empty($output) ) return false;
	
		//init statistics variables
		$total_time = 0;    //accumulated time
		$times_tried = 0;   //how many times tried
		$times_reached = 0; //how many times does the host is reached
		$time_max = 0; //max reached time
		$time_min = -1; //min reached time
	
		//analysis output lines
		for( $i=0; $i<count($output); $i++ ){
			$line = $output[$i];
				
			//check "time=xxx"
			$word = " time=";
			$position = strpos($line, $word);
			if ( $position === false) {
				$word = " time<"; //In Windows, ping result has "time<"
				$position = strpos($line, $word);
			}
				
			//if found "time=xxx"
			if ( $position ) {
				$times_tried++;
	
				//cut down time value from line
				$line = substr($line, $position + strlen($word));
				$words = explode(' ', $line, 2);
				$word = trim($words[0]);
	
				//if $word ends with "ms", cut it down
				if (substr($word, strlen($word)-2) === 'ms')
					$word = trim( substr($word, 0, strlen($word)-2) );
				
				//if find a number ( time value )
				if ( is_numeric($word) ) {
					$time = floatval($word);
					//update statistics
					$times_reached++;
					$total_time += $time;
					$time_max = ($time > $time_max) ? $time : $time_max;
					if ($time_min < 0)
						$time_min = $time;
					else
						$time_min = ($time < $time_min) ? $time : $time_min;
									
				}
				
				continue;
			}
				
			//check "timeout"
			$position = strpos($line, "timeout");
			if ($position) { $times_tried++; continue; }
				
			//check "timed out"
			$position = strpos($line, "timed out"); //In Windows, ping result has "timed out"
			if ($position) { $times_tried++; continue; }
			
		} //end of analysis output lines
	
		//return result
		if ($times_tried == 0) {
			return false;
		} else {
			if ($time_min<0) $time_min = 0;
			return array(
					'action'  => 'ping',
					'host'    => $host,
					'times'   => $times_tried,
					'reached' => $times_reached,
					'average' => $times_reached > 0 ? intval(round($total_time / $times_reached)) : 0 ,
					'max'     => intval( round($time_max) ),
					'min'     => intval( round($time_min) ),
			);
		}
	}
	
	/**
	 * convert IP address string to integer
	 * @param string $ipString IP address string, such as '192.168.1.12';
	 * @return int|bool, return ip integer if success. return false if error.
	 */
	private static function ipStringToInt($ipString) {
		if (!is_string($ipString)) return false;
		$arr = explode('.', trim($ipString));
		if (count($arr)!==4) return false;
		
		foreach ($arr as $key=>$value) {
			if (!is_numeric($value)) return false;
			$value = intval($value);
			if ($value<0 || $value>255) return false;
			$arr[$key] = $value;
		}
		
		//since PHP does not support unsigned int, the result may be negative
		return ($arr[0] << 24) + ($arr[1] << 16) + ($arr[2] << 8) + $arr[3];
	}
	
	/**
	 * convert IP address integer to string
	 * @param long $ip_int
	 */
	private static function ipIntToString($ip_int) {
		$first = ($ip_int & 0xFF000000) >> 24;
		if ($first<0) $first = 256 + $first;
		return  strval($first). "."
			   .strval(($ip_int & 0xFF0000) >> 16) . "."
			   .strval(($ip_int & 0xFF00) >> 8) . "."
			   .strval(($ip_int & 0xFF));
	}
	
	/**
	 * print a line
	 * @param string $str
	 */
	private static function println($str) {
		echo $str."\n";
		@ob_flush(); @flush();
	}
	
	/**
	 * whether socket is permitted
	 * 
	 * @return bool
	 */
	private static function canPerformSocket() {
		if ( Ping::$can_SOCK_RAW === null ) {
			$socket  = @socket_create(AF_INET, SOCK_RAW, 1);
			if ($socket===false)
				Ping::$can_SOCK_RAW = false;
			else
				Ping::$can_SOCK_RAW = true;
		}
		return Ping::$can_SOCK_RAW;
	}
	
	/**
	 * determin os
	 * @return string
	 */
	private static function whichOs() {
		if (Ping::$os == null) {
			$system = php_uname("s");
			if (strpos($system, 'Linux') !== false)
				Ping::$os = 'Linux';
			else if (strpos($system, 'Windows') !== false)
				Ping::$os = 'Windows';
			else if (strpos($system, 'Darwin') !== false)
				Ping::$os = 'Mac';
			else
				Ping::$os = 'Unknown';
		}
		return Ping::$os;
	}
}

?>