<?php

###############################################################################
# godaddy.php
#
# @author Anil Kumar <akumar@codepunch.com>
# @link   https://codepunch.com
#
############################################################################### 

namespace CodePunch\Whois;

use Exception;
use CodePunch\Whois\Whois as WHOIS;
use CodePunch\Base\Util as UTIL;

###############################################################################

class GoDaddy {
	
	private $api_url_base  		= 'https://api.godaddy.com/v1/domains/';
	private $api_access_key		= null;
	private $api_secret_key		= null;
	private $notfound_token 	= "No match for ";
	
	public static $whois_server	= "whois.godaddy.com";
	
	###########################################################################
	
	public function __construct($apikey, $apisecret)
	{ 
		$this->api_access_key = $apikey;
		$this->api_secret_key = $apisecret;
	}
	
	
	###########################################################################
	
	private function construct_http_header()
	{
		return array("Authorization: sso-key $this->api_access_key:$this->api_secret_key");
	}
	
	###########################################################################
	
	private function construct_url($domain)
	{
		return $this->api_url_base . $domain;
	}
	
	###########################################################################
	
	private function cleanup_keys(&$info)
	{
		$xlate = array(
		'contactRegistrantnameFirst' => 'Registrant First Name',
		'contactRegistrantnameLast' => 'Registrant Last Name',
		'contactRegistrantorganization' => 'Registrant Organization',
		'contactRegistrantemail' => 'Registrant Email',
		'contactRegistrantphone' => 'Registrant Phone',
		'contactRegistrantfax' => 'Registrant Fax',
		'contactRegistrantaddressMailingaddress1' => 'Registrant Address',
		'contactRegistrantaddressMailingaddress2' => 'Registrant Street',
		'contactRegistrantaddressMailingcity' => 'Registrant City',
		'contactRegistrantaddressMailingstate' => 'Registrant State',
		'contactRegistrantaddressMailingpostalCode' => 'Registrant ZIP',
		'contactRegistrantaddressMailingcountry' => 'Registrant Country',
		'contactBillingnameFirst' => 'Billing First Name',
		'contactBillingnameLast' => 'Billing Last Name',
		'contactBillingorganization' => 'Billing Organization',
		'contactBillingemail' => 'Billing Email',
		'contactBillingphone' => 'Billing Phone',
		'contactBillingfax' => 'Billing Fax',
		'contactBillingaddressMailingaddress1' => 'Billing Address',
		'contactBillingaddressMailingaddress2' => 'Billing Street',
		'contactBillingaddressMailingcity' => 'Billing City',
		'contactBillingaddressMailingstate' => 'Billing State',
		'contactBillingaddressMailingpostalCode' => 'Billing ZIP',
		'contactBillingaddressMailingcountry' => 'Billing Country',
		'contactAdminnameFirst' => 'Admin First Name',
		'contactAdminnameLast' => 'Admin Last Name',
		'contactAdminorganization' => 'Admin Organization',
		'contactAdminemail' => 'Admin Email',
		'contactAdminphone' => 'Admin Phone',
		'contactAdminfax' => 'Admin Fax',
		'contactAdminaddressMailingaddress1' => 'Admin Address',
		'contactAdminaddressMailingaddress2' => 'Admin Street',
		'contactAdminaddressMailingcity' => 'Admin City',
		'contactAdminaddressMailingstate' => 'Admin State',
		'contactAdminaddressMailingpostalCode' => 'Admin ZIP',
		'contactAdminaddressMailingcountry' => 'Admin Country',
		'contactTechnameFirst' => 'Tech First Name',
		'contactTechnameLast' => 'Tech Last Name',
		'contactTechorganization' => 'Tech Organization',
		'contactTechemail' => 'Tech Email',
		'contactTechphone' => 'Tech Phone',
		'contactTechfax' => 'Tech Fax',
		'contactTechaddressMailingaddress1' => 'Tech Address',
		'contactTechaddressMailingaddress2' => 'Tech Street',
		'contactTechaddressMailingcity' => 'Tech City',
		'contactTechaddressMailingstate' => 'Tech State',
		'contactTechaddressMailingpostalCode' => 'Tech ZIP',
		'contactTechaddressMailingcountry' => 'Tech Country',
		'expires' => 'Registrar Registration Expiration Date',
		'domainId' => 'Domain ID',
		'domain' => 'Domain',
		'status' => 'Status', 
		'expirationProtected' => 'Expiration Protected', 
		'holdRegistrar' => 'Registrar Hold',
		'locked' => 'Locked',
		'privacy' => 'Privacy',
		'renewAuto' => 'Auto Renew',
		'renewable' => 'Renewable',
		'renewDeadline' => 'Renew Deadline',
		'transferProtected' => 'Transfer Protected',
		'createdAt' => 'Creation Date',
		'nameServers0' => 'Name Server 0',
		'nameServers1' => 'Name Server 1',
		'nameServers2' => 'Name Server 2',
		'nameServers3' => 'Name Server 3',
		'nameServers4' => 'Name Server 4',
		'nameServers5' => 'Name Server 5',
		'nameServers6' => 'Name Server 6',
		'nameServers7' => 'Name Server 7',
		'nameServers8' => 'Name Server 8',
		);
		UTIL::array_xlate_keys($info, $xlate);
	}
	
	###########################################################################

	public function whois($domain, $fallback)
	{
		try {
			$url = $this->construct_url($domain);
			$header = $this->construct_http_header();
			$whois = UTIL::curl_get_url($url, 10, CURLAUTH_ANY, $header);
			$whois = $whois['result'];
			if($whois != "") {
				$whois = json_decode($whois, true);
				if(isset($whois['message'])) {
					if(strstr($whois['message'], "not found for shopper") !== false)
						$whois['message'] = "";
					throw new Exception($whois['message']);
				}
				else if(is_array($whois)) {
					$whois = UTIL::array_flatten($whois);
					unset($whois['authCode']);
					if(isset($whois['contactRegistrantnameFirst']))
						$registrant = $whois['contactRegistrantnameFirst'];
					if(isset($whois['contactRegistrantnameLast']))
						$registrant .= " " . $whois['contactRegistrantnameLast'];
					if(isset($registrant))
						$whois['Registrant Name'] = trim($registrant);
					$this->cleanup_keys($whois);
					if(isset($whois['code'])) {
						throw new Exception($whois['code']);
					}
					else
						$whois = UTIL::array_to_text($whois);
					return $whois;
				}
			}
			else
				throw new Exception("Unable to connect to GoDaddy API server");
		}
		catch (Exception $e) {
			if($fallback) 
				return WHOIS::redirect_to_port_43_whois($domain, self::$whois_server, $this->notfound_token, $e->getMessage());
		}
		return "";
	}
	
	###########################################################################
	
	public function domainlist()
	{
		$domains = array();
		
		$count = 1000;
		$lastdomain = "";
		while(1) {
			$added = 0;
			$url = $this->api_url_base . "?statusGroups=VISIBLE&limit={$count}&marker={$lastdomain}";
			$header = $this->construct_http_header();
			$info = UTIL::curl_get_url($url, 10, CURLAUTH_ANY, $header);
			$info = $info['result'];
			$info = json_decode($info, true);
			if(isset($info[0]['domain'])) {
				foreach($info as $d) {
					$domain = $d['domain'];
					$domains[] = $domain;
					$lastdomain = $domain;
					$added++;
				}
			}
			else if(isset($info['message'])) {
				throw new Exception($info['message']);
			}
			if(!$added || $lastdomain == "")
				break;
		}
		return $domains;
	}
}

###############################################################################

?>