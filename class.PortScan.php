<?php

/**
 * Port Scanner  ( a class to scan port on a server )
 * 
 * @author JoStudio
 * @version 1.0
 * 
 * @example <pre>
   //scan a single port
   $result = PortScan::scan($server, $port);
   if ($result) echo "$port is opened";
   
   //scan a range of port ( from 8000 to 8100), and print process on screen.
   $arr = PortScan::scanRange($server, 8000, 8100, true);
   if ($arr) print_r($arr); 
   
   </pre>
 */
class PortScan {
	
	/**
	 * scan one port of a server 
	 * @param string $server     server IP or server name
	 * @param int    $port       port number
	 * @param int    $timeout    (optional)timeout in milliseconds
	 * @return bool return true if the port is opened. return false if the port is not opened.
	 */
	public static function scan($server, $port, $timeout=300) {
		if ( $file = @fsockopen($server, $port, $errNo, $errStr, $timeout/1000) ) {
			fclose($file);
			return true;
		}
		return false;
	}
	
	/**
	 * scan a range of ports (form portStart to portEnd) of a server
	 * @param string $server     server IP or server name
	 * @param int    $portStart  first port number
	 * @param int    $portEnd    last port number
	 * @param bool   $printProcess (Optional)whether print info while processing 
	 * @return array,  return a array of opened ports. return false if error
	 * <pre>
	 *    format of return array:  
	 *    array ( 
	 *       port => name, //protocol of port
	 *       ...
	 *    ); 
	 * </pre>
	 */
	public static function scanRange($server, $portStart, $portEnd, $printProcess = true) {
		$result = array();
		$line = "";
		$count = 0;
		
		
		//check it: params must be integer
		if ( !is_numeric($portStart) || !is_numeric($portEnd) )
			return false;

		$portStart = intval($portStart);
		$portEnd = intval($portEnd);
		
		if ( $portEnd >= $portStart ) {
			$port = $portStart;
			$title = $port ."-". ($port+99). " : ";
			echo $title;
			
			while ($port <= $portEnd) {
				//scan on port
				if ( PortScan::scan($server, $port) ) {
					$name = PortNames::getName($port);
					$result[$port] =  $name;
					if ($printProcess) {
						echo "V";
						$name = empty($name) ? null : "($name)";
						$line .= str_pad("", strlen($title), " ") ."port $port $name opened\n";
					}
				} else {
					if ($printProcess) echo ".";
				}
				
				//next port
				$port++;
				
				//if count>=100, line break
				if (++$count >= 100 ) {
					$count=0;
					if ($printProcess) {
						echo "\n";
						if ( !empty($line) ) {
							echo "$line\n";
							$line = "";
						}
						$title = $port ."-". ($port+99). " : ";
						echo $title;
					}
					
				}
				
				//flush output
				if ($printProcess) { @ob_flush(); @flush(); }
			}
		}
		
		return count($result) > 0 ? $result : false;
	}
	
}


/**
 * A class stores protocol name of port
 * @author JoStudio
 */
class PortNames {

	public static $names = array(
		7 => "Echo Protocol",
		9 => "Wake-on-LAN",
		11 => "Active Users (systat service)",
		13 => "Daytime Protocol",
		15 => "Previously netstat service",
		17 => "Quote of the Day (QOTD)",
		18 => "Message Send Protocol",
		19 => "Character Generator Protocol (CHARGEN)",
		20 => "File Transfer Protocol (FTP)",
		21 => "File Transfer Protocol (FTP)",
		22 => "Secure Shell (SSH)",
		23 => "Telnet protocol¡ªunencrypted text communications",
		25 => "Simple Mail Transfer Protocol (SMTP)",
		37 => "Time Protocol",
		38 => "Route Access Protocol (RAP)",
		39 => "Resource Location Protocol (RLP)",
		42 => "Host Name Server Protocol",
		43 => "WHOIS protocol",
		49 => "TACACS+ Login Host protocol",
		50 => "Remote Mail Checking Protocol",
		51 => "Previously Interface Message Processor logical address management",
		52 => "Xerox Network Systems (XNS)",
		53 => "Domain Name System (DNS)",
		54 => "Xerox Network Systems (XNS)",
		56 => "Xerox Network Systems (XNS)",
		57 => "Any private terminal access",
		58 => "Xerox Network Systems (XNS)",
		67 => "Bootstrap Protocol (BOOTP)",
		68 => "Bootstrap Protocol (BOOTP)",
		69 => "Trivial File Transfer Protocol (TFTP)",
		70 => "Gopher protocol",
		71 => "NETRJS protocol",
		72 => "NETRJS protocol",
		73 => "NETRJS protocol",
		74 => "NETRJS protocol",
		77 => "Any private Remote job entry",
		79 => "Finger protocol",
		80 => "Hypertext Transfer Protocol (HTTP)",
		81 => "TorPark onion routing",
		88 => "Kerberos authentication system",
		90 => "dnsix (DoD Network Security for Information Exchange)",
		90 => "PointCast (dotcom)",
		99 => "WIP message protocol",
		101 => "NIC host name",
		102 => "ISO Transport Service Access Point (TSAP)",
		104 => "Digital Imaging and Communications in Medicine (DICOM",
		105 => "CCSO Nameserver",
		107 => "Remote User Telnet Service (RTelnet)",
		108 => "IBM Systems Network Architecture (SNA)",
		109 => "Post Office Protocol",
		110 => "Post Office Protocol",
		111 => "Open Network Computing Remote Procedure Call (ONC RPC113",
		113 => "Authentication Service (auth)",
		115 => "Simple File Transfer Protocol",
		117 => "UUCP Mapping Project (path service)",
		118 => "Structured Query Language (SQL)",
		119 => "Network News Transfer Protocol (NNTP)",
		135 => "Microsoft EPMAP (End Point Mapper)",
		139 => "NetBIOS Session Service",
		143 => "Internet Message Access Protocol (IMAP)",
		153 => "Simple Gateway Monitoring Protocol (SGMP)",
		158 => "Distributed Mail System Protocol (DMSP)",
		161 => "Simple Network Management Protocol (SNMP)",
		162 => "Simple Network Management Protocol Trap (SNMPTRAP)",
		170 => "Print server",
		177 => "X Display Manager Control Protocol (XDMCP)",
		194 => "Internet Relay Chat (IRC)",
		199 => "SNMP multiplexing protocol (SMUX)",
		201 => "AppleTalk Routing Maintenance",
		209 => "Quick Mail Transfer Protocol",
		210 => "ANSI Z39.50",
		213 => "Internetwork Packet Exchange (IPX)",
		218 => "Message posting protocol (MPP)",
		220 => "Internet Message Access Protocol (IMAP)",
		262 => "Arcisdms",
		264 => "Border Gateway Multicast Protocol (BGMP)",
		280 => "http-mgmt",
		300 => "ThinLinc Web Access",
		308 => "Novastor Online Backup",
		311 => "Mac OS X Server Admin (officially AppleShare IP Web administration)",
		318 => "PKIX Time Stamp Protocol (TSP)",
		350 => "Mapping of Airline Traffic over Internet Protocol (MATIP)",
		351 => "MATIP type B",
		356 => "cloanto-net-1 (used by Cloanto Amiga Explorer and VMs)",
		366 => "On-Demand Mail Relay (ODMR)",
		369 => "Rpc2portmap",
		370 => "codaauth2370",
		383 => "HP data alarm manager",
		384 => "A Remote Network Server System",
		387 => "AURP (AppleTalk Update-based Routing Protocol)",
		389 => "Lightweight Directory Access Protocol (LDAP)",
		399 => "Digital Equipment Corporation DECnet (Phase V+)",
		401 => "Uninterruptible power supply (UPS)",
		427 => "Service Location Protocol (SLP)",
		433 => "NNSP",
		4434 => "Mobile IP Agent (RFC 5944)",
		443 => "Hypertext Transfer Protocol over TLS/SSL (HTTPS)",
		444 => "Simple Network Paging Protocol (SNPP)",
		445 => "Microsoft-DS Active Directory",
		464 => "Kerberos Change/Set password",
		465 => "URL Rendezvous Directory for SSM (Cisco protocol)",
		465 => "Authenticated SMTP over TLS/SSL (SMTPS)",
		475 => "tcpnethaspsrv",
		491 => "GO-Global remote access and application publishing software",
		497 => "Dantz Retrospect",
		500 => "Internet Security Association and Key Management Protocol (ISAKMP)",
		502 => "Modbus Protocol",
		504 => "Citadel",
		510 => "FirstClass Protocol (FCP)",
		514 => "Remote Shell",
		515 => "Line Printer Daemon (LPD)",
		520 => "efs520",
		521 => "Routing Information Protocol Next Generation (RIPng)",
		524 => "NetWare Core Protocol (NCP)",
		531 => "AOL Instant Messenger",
		532 => "netnews",
		540 => "Unix-to-Unix Copy Protocol (UUCP)",
		542 => "commerce (Commerce Applications)",
		543 => "klogin",
		545 => "OSIsoft PI (VMS)",
		547 => "DHCPv6 server",
		548 => "Apple Filing Protocol (AFP)",
		550 => "new-rwho",
		554 => "Real Time Streaming Protocol (RTSP)",
		556 => "Remotefs",
		563 => "NNTP over TLS/SSL (NNTPS)",
		564 => "9P (Plan 9)",
		585 => "De-registered (with recommendation to use port 993 instead)",
		585 => "Legacy use of Internet Message Access Protocol over TLS/SSL (IMAPS)",
		587 => "e-mail message submission (SMTP)",
		591 => "FileMaker 6.0 (and later)",
		604 => "TUNNEL profile623",
		625 => "Open Directory Proxy (ODProxy)",
		631 => "Internet Printing Protocol (IPP)",
		631 => "Common Unix Printing System (CUPS)",
		635 => "RLZ DBase",
		636 => "Lightweight Directory Access Protocol over TLS/SSL (LDAPS)",
		639 => "MSDP",
		641 => "SupportSoft Nexus Remote Command (control/listening)",
		646 => "Label Distribution Protocol (LDP)",
		648 => "Registry Registrar Protocol (RRP)",
		651 => "IEEE-MMS",
		653 => "SupportSoft Nexus Remote Command (data)",
		655 => "Tinc VPN daemon",
		657 => "IBM RMC (Remote monitoring and Control)",
		688 => "REALM-RUSD (ApplianceWare Server Appliance Management Protocol)",
		690 => "Velneo Application Transfer Protocol (VATP)",
		691 => "MS Exchange Routing",
		694 => "Linux-HA high-availability heartbeat",
		695 => "IEEE Media Management System over SSL (IEEE-MMS-SSL)",
		698 => "Optimized Link State Routing (OLSR)",
		700 => "Extensible Provisioning Protocol (EPP)",
		706 => "Secure Internet Live Conferencing (SILC)",
		711 => "Cisco Tag Distribution Protoco",
		712 => "Topology Broadcast based on Reverse-Path Forwarding routing protocol (TBRPF",
		749 => "Kerberos (protocol)",
		750 => "kerberos-iv",
		751 => "kerberos_master",
		752 => "passwd_server",
		753 => "Reverse Routing Header (RRH)",
		753 => "Reverse Routing Header (RRH)",
		753 => "userreg_server",
		754 => "tell send",
		760 => "krbupdate ",
		782 => "Conserver serial-console management server",
		783 => "SpamAssassin spamd daemon",
		800 => "mdbs-daemon",
		808 => "Microsoft Net.TCP Port Sharing Service",
		829 => "Certificate Management Protocol",
		830 => "NETCONF over SSH",
		831 => "NETCONF over BEEP",
		832 => "NETCONF for SOAP over HTTPS",
		833 => "NETCONF for SOAP over BEEP",
		843 => "Adobe Flash",
		847 => "DHCP Failover protocol",
		848 => "Group Domain Of Interpretation (GDOI)",
		853 => "DNS over TLS (RFC 7858)",
		860 => "iSCSI (RFC 3720)",
		861 => "OWAMP control (RFC 4656)",
		862 => "TWAMP control (RFC 5357)",
		873 => "rsync file synchronization protocol",
		888 => "IBM Endpoint Manager Remote Control",
		897 => "Brocade SMI-S RPC",
		898 => "Brocade SMI-S RPC SSL",
		902 => "VMware ESXi",
		903 => "VMware ESXi",
		944 => "Network File System Service",
		953 => "BIND remote name daemon control (RNDC)",
		981 => "Remote HTTPS management for firewall devices running embedded Check Point VPN-1 software",
		987 => "Microsoft Windows SBS SharePoint",
		989 => "FTPS Protocol (data)",
		992 => "Telnet protocol over TLS/SSL",
		993 => "Internet Message Access Protocol over TLS/SSL (IMAPS)",
		994 => "Internet Relay Chat over TLS/SSL (IRCS)",
		995 => "Post Office Protocol 3 over TLS/SSL (POP3S)",
		999 => "ScimoreDB Database System",
		1010 => "ThinLinc web-based administration interface[self-published source?]",
		8080 => "HTTP maybe",
	);
	
	/**
	 * return name of a port. return null if port is unknown
	 * @param int $port
	 * @return string, return name of a port. return null if port is unknown
	 */
	public static function getName($port) {
		if ( isset( PortNames::$names[$port] ) ) {
			return PortNames::$names[$port];
		}
		return null;
	}
}

?>