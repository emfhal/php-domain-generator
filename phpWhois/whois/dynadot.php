<?php

###############################################################################
# dynadot.php
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

class Dynadot {
	
	private $api_url_base  		= 'https://api.dynadot.com/api3.xml';
	private $api_access_key		= null;
	private $contact_info 		= array();
	private $notfound_token 	= "No match for domain ";
	
	public static $whois_server	= "whois.dynadot.com";
	
	###########################################################################
	
	public function __construct($apikey)
	{ 
		$this->api_access_key = $apikey;
	}
	
	###########################################################################
	
	private function construct_url($command, $param, $value)
	{
		return $this->api_url_base . "?key={$this->api_access_key}&command={$command}&{$param}={$value}";
	}
	
	###########################################################################

	private function get_contact_info($contactid)
	{
		if(isset($this->contact_info[$contactid]))
			return $this->contact_info[$contactid];
		$url = $this->construct_url("get_contact", "contact_id", $contactid);
		$contactinfo = UTIL::curl_get_url($url, 10);
		if($contactinfo !== false)
		{
			$contactinfo = $contactinfo['result'];
			$contactinfo = simplexml_load_string($contactinfo);
			if(isset($contactinfo->GetContactHeader->Status) && isset($contactinfo->GetContactContent->Contact)) {
				if($contactinfo->GetContactHeader->Status == "success") {
					$cinfo = $contactinfo->GetContactContent->Contact;
					$contactdata = json_decode(json_encode($cinfo), TRUE);
					$this->contact_info[$contactid] = $contactdata;
					return $contactdata;
				}
			}
		}
		return false;
	}
	
	###########################################################################

	private function parse_contact_info(&$whoisdata, $contacttype)
	{
		if(isset($whoisdata['Whois'][$contacttype]['ContactId'])) {
			$cid = $whoisdata['Whois'][$contacttype]['ContactId'];
			$contactdata = $this->get_contact_info($cid);
			foreach ($contactdata as $k => $v) {
				$whoisdata[$contacttype.$k] = $v;
			}
			unset($whoisdata['Whois'][$contacttype]);
		}
	}
	
	###########################################################################
	
	private function cleanup_keys(&$info)
	{
		$xlate = array(
		'Name' => 'Domain',
		'Expiration' => 'Registrar Registration Expiration Date',
		'Registration' => 'Creation Date',
		'RegistrantUnverified' => 'Registrant Unverified',
		'RenewOption' => 'Renew Option',
		'RegistrantContactId' => 'Registrant ContactId',
		'RegistrantOrganization' => 'Registrant Organization',
		'RegistrantName' => 'Registrant Name',
		'RegistrantEmail' => 'Registrant Email',
		'RegistrantPhoneCc' => 'Registrant Phone Country',
		'RegistrantPhoneNum' => 'Registrant Phone',
		'RegistrantAddress1' => 'Registrant Address',
		'RegistrantAddress2' => 'Registrant Street',
		'RegistrantCity' => 'Registrant City',
		'RegistrantState' => 'Registrant State',
		'RegistrantZipCode' => 'Registrant ZIP',
		'RegistrantCountry' => 'Registrant Country',
		'RegistrantGtldVerified' => 'Registrant Gtld Verified',
		'AdminContactId' => 'Admin ContactId',
		'AdminOrganization' => 'Admin Organization',
		'AdminName' => 'Admin Name',
		'AdminEmail' => 'Admin Email',
		'AdminPhoneCc' => 'Admin Phone Country',
		'AdminPhoneNum' => 'Admin Phone',
		'AdminAddress1' => 'Admin Address',
		'AdminAddress2' => 'Admin Street',
		'AdminCity' => 'Admin City',
		'AdminState' => 'Admin State',
		'AdminZipCode' => 'Admin Zip',
		'AdminCountry' => 'Admin Country',
		'AdminGtldVerified' => 'Admin Gtld Verified',
		'TechnicalContactId' => 'Technical ContactId',
		'TechnicalOrganization' => 'Technical Organization',
		'TechnicalName' => 'Technical Name',
		'TechnicalEmail' => 'Technical Email',
		'TechnicalPhoneCc' => 'Technical Phone Country',
		'TechnicalPhoneNum' => 'Technical Phone',
		'TechnicalAddress1' => 'Technical Address',
		'TechnicalAddress2' => 'Technical Street',
		'TechnicalCity' => 'Technical City',
		'TechnicalState' => 'Technical State',
		'TechnicalZipCode' => 'Technical Zip',
		'TechnicalCountry' => 'Technical Country',
		'TechnicalGtldVerified' => 'Technical Gtld Verified',
		'BillingContactId' => 'Billing ContactId',
		'BillingOrganization' => 'Billing Organization',
		'BillingName' => 'Billing Name',
		'BillingEmail' => 'Billing Email',
		'BillingPhoneCc' => 'Billing Phone Country',
		'BillingPhoneNum' => 'Billing Phone',
		'BillingAddress1' => 'Billing Address',
		'BillingAddress2' => 'Billing Street',
		'BillingCity' => 'Billing City',
		'BillingState' => 'Billing State',
		'BillingZipCode' => 'Billing Zip',
		'BillingCountry' => 'Billing Country',
		'BillingGtldVerified' => 'Billing Gtld Verified'
		);
		UTIL::array_xlate_keys($info, $xlate);
	}
	
	###########################################################################
	# Merge the Phone country codes with phone numbers and add the phone entry,
	# then remove the superfluous keys.
	private function cleanup_phone_nos(&$info)
	{
		$xlate = array('Tech Phone'=> array('TechnicalPhoneCc','TechnicalPhoneNum'),  'Billing Phone'=> array('BillingPhoneCc','BillingPhoneNum'),
					'Admin Phone'=> array('AdminPhoneCc','AdminPhoneNum'),  'Registrant Phone'=> array('RegistrantPhoneCc','RegistrantPhoneNum'));
					
		foreach($xlate as $key=>$val) {
			if(isset($info[$val[0]]) && isset($info[$val[1]])) {
				# Also remove spaces from the phone number.
				$info[$key] = "+" . $info[$val[0]] . "." . str_ireplace(" ", "", $info[$val[1]]);
				unset($info[$val[0]]);
				unset($info[$val[1]]);
			}
		}
	}
	
	###########################################################################
	
	public function is_processing()
	{
		$url = $this->construct_url("is_processing", "", "");
		$udata = UTIL::curl_get_url($url, 10);
		$udata = $udata['result'];
		if($udata !== "")
		{
			$udata = simplexml_load_string($udata);
			$udata = json_decode(json_encode($udata), TRUE);
			if(isset($udata['ResponseHeader']['ResponseMsg']))
			{
				if($udata['ResponseHeader']['ResponseMsg'] == "yes")
					return true;
			}
		}
		return false;
	}
	
	###########################################################################
	
	public function whois($domain, $fallback=false)
	{
		try
		{
			if($this->is_processing())
				throw new Exception("Dynadot API server is busy");
			else
			{
				$url = $this->construct_url("domain_info", "domain", $domain);
				$udata = UTIL::curl_get_url($url, 10);
				// curl_get_url will always return an array
				$whois = $udata['result'];
				if($whois != "") {
					$whoisdata = simplexml_load_string($whois);
					if($whoisdata !== false) {
						if(isset($whoisdata->DomainInfoResponseHeader->Status)) {
							if($whoisdata->DomainInfoResponseHeader->Status == "success" && isset($whoisdata->DomainInfoContent->Domain)) {
								$cinfo = $whoisdata->DomainInfoContent->Domain;
								$whoisdata = json_decode(json_encode($cinfo), TRUE);
								if(isset($whoisdata['Expiration'])) {
									$expdate = $whoisdata['Expiration'];
									$whoisdata['Expiration'] = date("Y-M-d", $expdate/1000);
								}
								if(isset($whoisdata['Registration'])) {
									$expdate = $whoisdata['Registration'];
									$whoisdata['Registration'] = date("Y-M-d", $expdate/1000);
								}
								if(isset($whoisdata['NameServerSettings']['NameServers']['ServerName'])) {
									$nsdata = array_filter($whoisdata['NameServerSettings']['NameServers']['ServerName']);
									unset($whoisdata['NameServerSettings']);
									$whoisdata['NameServers'] = $nsdata;
								}
								unset($whoisdata['Folder']);
								$this->parse_contact_info($whoisdata, 'Registrant');
								$this->parse_contact_info($whoisdata, 'Admin');
								$this->parse_contact_info($whoisdata, 'Technical');
								$this->parse_contact_info($whoisdata, 'Billing');
								$whoisdata = array_filter($whoisdata);
								$this->cleanup_phone_nos($whoisdata);
								$this->cleanup_keys($whoisdata);
								ksort($whoisdata);
								$whoisdata = UTIL::array_to_text($whoisdata);
								return $whoisdata;
							}
							else if(isset($whoisdata->DomainInfoResponseHeader->Error)) {
								if($whoisdata->DomainInfoResponseHeader->Error == "could not find domain in your account")
									$whoisdata->DomainInfoResponseHeader->Error = "Domain is not present in your profile";
								throw new Exception($whoisdata->DomainInfoResponseHeader->Error);
							}
						}
						else if(isset($whoisdata->ResponseHeader->Error)) {
							throw new Exception($whoisdata->ResponseHeader->Error);
						}
					}
				}
				else
					throw new Exception("Unable to connect to Dynadot API server");
			}
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
		if($this->is_processing())
			throw new Exception("Dynadot API server is busy");
		else
		{
			$url = $this->api_url_base . "?key={$this->api_access_key}&command=list_domain";
			$info = UTIL::curl_get_url($url, 10);
			if($info['result'] != "")
			{
				$info = $info['result'];
				$info = simplexml_load_string($info);
				$domaininfo = json_decode(json_encode($info), TRUE);
				if(isset($domaininfo['ListDomainInfoContent']['DomainInfoList']['DomainInfo'])) {
					$info = $domaininfo['ListDomainInfoContent']['DomainInfoList']['DomainInfo'];
					foreach($info as $i) {
						if(isset($i['Domain']['Name']))
							$domains[] = $i['Domain']['Name'];
					}
				}
				else if(isset($domaininfo['ResponseHeader']['Error'])) {
					throw new Exception($domaininfo['ResponseHeader']['Error']);
				}
			}
			else 
				throw new Exception("Unable to connect to Dynadot API server");
		}
		return $domains;
	}
}

###############################################################################

?>