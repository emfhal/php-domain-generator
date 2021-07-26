<?php

###############################################################################
# whoapi.php
#
# @author Anil Kumar <akumar@codepunch.com>
# @link   https://codepunch.com
#
# A Simple Whois Client that uses Registrar APIs where available.
#
############################################################################### 

use CodePunch\Base\Util as UTIL;

###############################################################################

if(file_exists(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php")))
	require_once('config.php');
else
{
	$heading = "Missing Configuration File.";
	$body = "";
	if(file_exists(realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config-sample.php"))) {
		$body .= "Installation is not complete. ";
		$body .= "Please rename whois/config-sample.php to whois/config.php and edit it.";
	}
	if(!UTIL::is_cli())
		$message = "<h3>$heading</h3><hr><p>$body</p>";
	else
		$message = "$heading\n$body\n";
	echo $message;
	exit;
}

###############################################################################

require_once('dynadot.php');
require_once('godaddy.php');
require_once('resellerclub.php');
require_once('whois.php');

###############################################################################

function api_whois_authenticate()
{
	global $api_whois_access_key;
	if($api_whois_access_key != "") {
		$key = UTIL::get_request_string("k");
		if($key != $api_whois_access_key) {
			header('HTTP/1.0 403 Forbidden');
			echo 'Access denied!';
			exit;
		}
	}
}

###############################################################################

function get_registrar_instance($server)
{
	global $gd_api_access_key, $gd_api_secret_key;
	global $dn_api_access_key;
	global $rsclub_api_access_key, $rsclub_customer_id;
	
	$whoapi = null;
	if($server != "") {
		if(strcasecmp(CodePunch\Whois\Dynadot::$whois_server, $server) == 0 || stristr($server, "dynadot") !== false)
			$whoapi = new CodePunch\Whois\Dynadot($dn_api_access_key);
		else if(strcasecmp(CodePunch\Whois\GoDaddy::$whois_server, $server) == 0 || stristr($server, "godaddy") !== false)
			$whoapi = new CodePunch\Whois\GoDaddy($gd_api_access_key, $gd_api_secret_key);
		else if(strcasecmp(CodePunch\Whois\ResellerClub::$whois_server, $server) == 0 || stristr($server, "resellerclub") !== false)
			$whoapi = new CodePunch\Whois\ResellerClub($rsclub_api_access_key, $rsclub_customer_id);
	}
	return $whoapi;
}

###############################################################################

function api_whois($domain, $server)
{
	if($server == "" && $domain != "")
		$server = CodePunch\Whois\Whois::find_whois_server($domain);

	$whoapi = get_registrar_instance($server);

	$ascii = CodePunch\Whois\Whois::idn_convert($domain);
	
	if($whoapi)
		$whois = $whoapi->whois($ascii, true);
	else 
		$whois = UTIL::whois($ascii, $server, 43, 5);
	$whois = trim($whois);
	return $whois;
}

###############################################################################

function get_supported_servers()
{
	return array(
	CodePunch\Whois\Dynadot::$whois_server,
	CodePunch\Whois\GoDaddy::$whois_server,
	CodePunch\Whois\ResellerClub::$whois_server
	);
}

###############################################################################

function get_domain_list($server)
{
	$whoapi = $whoapi = get_registrar_instance($server);
	if($whoapi)
		return $whoapi->domainlist();
	else
		throw new Exception("Not supported ({$server})");
}

###############################################################################

?>