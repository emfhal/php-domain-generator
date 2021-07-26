<?php

###############################################################################
# whois.php
#
# @author Anil Kumar <akumar@codepunch.com>
# @link   https://codepunch.com
#
############################################################################### 

namespace CodePunch\Whois;

use Exception;

require_once('idna_convert.class.php');

###############################################################################

use CodePunch\Base\Util as UTIL;

###############################################################################

class Whois {
	
	public $whois_port 	= 43;
	public $whois_server = null;
	public $whois_db_path = null;
	
	public $db_connect_handle = null;
	public $last_error = null;
	
	###########################################################################
	
	public function __construct($dbpath=null)
	{ 
		$this->db_connect_handle = null;
		if($dbpath !== null)
		{
			$this->whois_db_path = $dbpath;
			if(is_writable($this->whois_db_path))
			{
				try 
				{
					$this->db_connect_handle = new \PDO('sqlite:' . $this->whois_db_path);
					$this->db_connect_handle->exec("CREATE TABLE if not exists TLDS (TLD TEXT PRIMARY KEY, Server TEXT)"); 
					$this->db_connect_handle->exec("CREATE TABLE if not exists WhoisServers (Server TEXT PRIMARY KEY, Interval INTEGER, LastConnect INTEGER)"); 
				}
				catch (\PDOException $e) 
				{
					$this->set_error($e->getMessage());
					$this->db_connect_handle = null;
				}
				if($this->db_connect_handle)
				{
					$this->db_connect_handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					$this->db_connect_handle->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
				}
			}
			else
				$this->set_error("Data file '$dbpath' is not writable");
		}
		else
			$this->set_error("Missing data file.");
	}
	
	###########################################################################
	
	function is_db_open()
	{
		return $this->db_connect_handle == null ? false : true;
	}
	
	###########################################################################
	
	function get_db_row($server)
	{
		if($this->is_db_open())
		{
			try 
			{
				$fmode = \PDO::FETCH_ASSOC;
				$sql = 'SELECT * FROM WhoisServers WHERE Server=?';
				$stmt = $this->db_connect_handle->prepare($sql);
				$stmt->execute(array($server));
				$rows = $stmt->fetchAll($fmode);
				if(is_array($rows))
				{
					if(count($rows))
						return $rows[0];
				}
			}
			catch (\PDOException $e) 
			{
				$this->set_error($e->getMessage());
			}
		}
		return false;
	}
	
	###########################################################################
	
	function init_whois_server($server, $interval=10)
	{
		if(is_db_open())
		{
			$sql = "INSERT OR IGNORE INTO WhoisServers(Server, Interval, LastConnect) VALUES(?, ?, ?)";	
			try 
			{
				 $stmt = $this->db_connect_handle->prepare($sql);
				 $stmt->execute(array($server,$interval, 0));
				 return true;
			}
			catch (\PDOException $e) 
			{
				$this->set_error($e->getMessage());
			}
		}
		return false;
	}
	
	###########################################################################
	
	function set_interval($server, $interval)
	{
		if(is_db_open())
		{
			$rows = $this->get_db_row($server);
			if($rows === false)
				return $this->init_whois_server($server, $interval);
			else
			{
				$sql = "UPDATE WhoisServers SET Interval=? WHERE Server=?";	
				try 
				{
					 $stmt = $this->db_connect_handle->prepare($sql);
					 $stmt->execute(array($interval, $server));
					 return true;
				}
				catch (\PDOException $e) 
				{
					$this->set_error($e->getMessage());
				}
			}
		}
		return false;
	}
	
	###########################################################################
	
	function set_last_connect($server, $connectedat=0)
	{
		if(is_db_open())
		{
			$rows = $this->get_db_row($server);
			if($rows === false)
				$this->init_whois_server($server);
			$sql = "UPDATE WhoisServers SET LastConnect=? WHERE Server=?";	
			try 
			{
				if($connectedat == 0)
					$connectedat = time();
				 $stmt = $this->db_connect_handle->prepare($sql);
				 $stmt->execute(array($connectedat, $server));
				 return true;
			}
			catch (\PDOException $e) 
			{
				$this->set_error($e->getMessage());
			}
		}
		return false;
	}
	
	###########################################################################
	
	function get_last_connect($server)
	{
		$rows = $this->get_db_row($server);
		if($rows !== false)
			return  $rows['LastConnect'];
		else
			return false;
	}
	
	###########################################################################
	
	function get_interval($server)
	{
		$rows = $this->get_db_row($server);
		if($rows !== false)
			return  $rows['Interval'];
		else
			return false;
	}
	
	###########################################################################
	
	function set_server_for_tld($tld, $server)
	{
		if($this->is_db_open())
		{
			$sql = "INSERT OR IGNORE INTO TLDS(tld, server) VALUES(?, ?)";	
			try 
			{
				 $stmt = $this->db_connect_handle->prepare($sql);
				 $stmt->execute(array($tld,$server));
				 return true;
			}
			catch (\PDOException $e) 
			{
				$this->set_error($e->getMessage());
			}
		}
		return false;
	}
	
	###########################################################################
	
	public function get_server_for_tld($tld)
	{
		if($this->is_db_open())
		{
			try 
			{
				$fmode = \PDO::FETCH_ASSOC;
				$sql = 'SELECT * FROM TLDS WHERE tld=?';
				$stmt = $this->db_connect_handle->prepare($sql);
				$stmt->execute(array($tld));
				$rows = $stmt->fetchAll($fmode);
				if(is_array($rows))
				{
					if(count($rows))
						return $rows[0];
				}
			}
			catch (\PDOException $e) 
			{
				$this->set_error($e->getMessage());
			}
		}
		return false;
	}
	
	###########################################################################
	
	function verify_db()
	{
		if($this->is_db_open())
		{
			$server = "dummy.server.local";
			$cat = time()-1000;
			if($this->init_whois_server($server) !== false)
			{
				if($this->set_last_connect($server, $cat) !== false)
				{
					$lct = $this->get_last_connect($server);
					if($lct !== false && $lct == $cat)
						return true;
				}
			}
		}
		return false;
	}
	
	###########################################################################
	
	public function set_error($msg)
	{
		$this->last_error[] = $msg;
		error_log($msg);
	}
	
	###########################################################################
	
	public function clear_errors()
	{
		unset($this->last_error);
	}
	
	###########################################################################
	
	public function get_errors()
	{
		if(isset($this->last_error))
			return $this->last_error;
		else
			return null;
	}
	
	###########################################################################
	
	public function get_last_error()
	{
		return $this->getError();
	}
	
	###########################################################################
	
	public function get_error($index = -1)
	{
		if(isset($this->last_error))
		{
			$count = count($this->last_error);
			if($index < 0)
				$index = $count-1;
			if($index >= 0 && $index < $count)
				return $this->last_error[$index];
		}
		return "";
	}
	
	###########################################################################
	
	public function set_port($port) {$this->whois_port = $port;}
	public function set_server($server) {$this->whois_server = $server;}
	
	###########################################################################
	
	public function run_whois($query, $server=false, $port=false)
	{
		if($port !== false)
			$this->set_port($port);
		if($server !== false)
			$this->set_server($server);
		return UTIL::whois($query, $this->whois_server, $this->whois_port);
	}
	
	###########################################################################
	
	public function whois($domain, $cl="")
	{
		$tld = $domain;
		$parts = explode(".", $domain);
		if(count($parts) > 1)
			$tld = $parts[count($parts)-1];
		$info = $this->get_server_for_tld($tld);
		if($info === false)
			$info = UTIL::find_whois_info($domain);
		if($info !== false && isset($info['server']))
		{
			$server = $info['server'];
			if($cl == "" && (strtolower($tld) == "com" || strtolower($tld) == "net"))
				$cl = "domain";
			$query = $cl == "" ? $domain : "$cl $domain";
			return $this->run_whois($query, $server);
		}
		return false;
	}
	
	###########################################################################

	public static function idn_convert($domain)
	{
		$IDN = new \idna_convert(array('idn_version' => 2008));
		return strtolower($IDN->encode($domain)); 
	}

	###########################################################################

	public static function idn_reconvert($ascii_domain)
	{
		$IDN = new \idna_convert(array('idn_version' => 2008));
		return $IDN->decode($ascii_domain); 
	}

	###########################################################################

	public static function idn_convert_to_host_name($domain, $subdomain)
	{
		$ascii_domain    = self::idn_convert($domain);
		$ascii_subdomain = self::idn_convert($subdomain);
		return ($subdomain == "" ? "" : ($ascii_subdomain . ".")) . $ascii_domain; 
	}
	
	
	###########################################################################

	public static function find_whois_info($domain_name)
	{
		$wserver = "";
		$registry = "";
		$tld = $domain_name;
		$parts = explode(".", $domain_name);
		if(count($parts) > 1)
			$tld = $parts[count($parts)-1];
		$ascii_name = self::idn_convert($tld);
		$url = "http://www.iana.org/domains/root/db/$ascii_name.html";
		$result = @file_get_contents($url);
		if($result !== false)
		{
			$p1 = strip_tags(UTIL::get_string_between($result, "<b>URL for registration services:</b>", "\n"));
			$registry = trim($p1);
			$p2 = strip_tags(UTIL::get_string_between($result, "<b>WHOIS Server:</b>", "\n"));
			$wserver = trim($p2);
		}
		if($wserver == "")
		{
			$tserver = "whois.nic." . $tld;
			if($tld = "com") $tserver = "whois.crsnic.net";
			$ip = gethostbyname($tserver);
			if($ip != $tserver)
				$wserver = $tserver;
		}
			
		$info['server'] = $wserver;
		$info['registry'] = $registry;
		$info['tld'] = $tld;
		return $info;
	}
	
	###########################################################################
	
	public static function find_whois_server($domain)
	{
		$info = self::find_whois_info($domain);
		if($info !== false && isset($info['server']))
			return $info['server'];
		else
			return "";
	}
	
	###########################################################################
	
	public static function redirect_to_port_43_whois($domain, $server, $notfoundtoken, $errormsg)
	{
		$whois = UTIL::whois($domain, $server, 43, 5);
		if(stristr($whois, $notfoundtoken) !== false) {
			throw new Exception("Wrong Whois Server or Domain Not Found");
		}
		return "" . trim($whois);
	}
}

###############################################################################

/*
	require_once('config.php');
	use CodePunch\Whois\Whois as W;
	
	$dbpath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . ".whois.db";
	$test = new W($dbpath);
	
	*/

###############################################################################

?>
