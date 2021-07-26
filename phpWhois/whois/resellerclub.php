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

class ResellerClub {
	
	private $api_url_base  		= 'https://httpapi.com/api/domains/';
	private $api_access_key		= null;
	private $api_customer_id	= null;
	private $notfound_token 	= "No match for ";
	
	public static $whois_server	= "whois.publicdomainregistry.com";
	
	###########################################################################
	
	public function __construct($apikey, $customerid)
	{ 
		$this->api_access_key = $apikey;
		$this->api_customer_id = intval($customerid);
	}
	
	###########################################################################
	
	private function get_orderid($domain)
	{
		$url = "{$this->api_url_base}orderid.json?auth-userid={$this->api_customer_id}&api-key={$this->api_access_key}&domain-name={$domain}";
		$udata = UTIL::curl_get_url($url);
		$udata = $udata['result'];
		if(stristr($udata, "{") !== false && stristr($udata, "\"message\"") !== false) {
			$udata = json_decode($udata, true);
			if(isset($udata['message'])) 
				throw new Exception($udata['message']);
		}
		return $udata;
	}

	###########################################################################

	private function get_orderinfo($orderid)
	{
		$url = "{$this->api_url_base}details.json?auth-userid={$this->api_customer_id}&api-key={$this->api_access_key}&order-id={$orderid}&options=All";
		$result = UTIL::curl_get_url($url);
		$result = $result['result'];
		if($result !== "")
			return json_decode($result, true);
		return false;
	}
	
	###########################################################################
	
	private function parse_contact_info(&$whoisdata, $contacttype)
	{
		if(isset($whoisdata[$contacttype])) {
			$contactdata = $whoisdata[$contacttype];
			foreach ($contactdata as $k => $v) {
				if($k != "contacttype")
					$whoisdata[$contacttype.$k] = $v;
			}
			unset($whoisdata[$contacttype]);
			unset($whoisdata[$contacttype."id"]);
		}
	}
	
	###########################################################################
	
	private function cleanup_keys(&$info)
	{
		$xlate = array(
		'domainname' => 'Domain Name',
		'currentstatus' => 'Domain Status',
		'orderstatus0' => 'Order Status',
		'billingcontactname' => 'Billing Name',
		'billingcontactcompany' => 'Billing Company',
		'billingcontactaddress1' => 'Billing Address',
		'billingcontactaddress2' => 'Billing Street',
		'billingcontactaddress3' => 'Billing Address 2',
		'billingcontactcity' => 'Billing City',
		'billingcontactzip' => 'Billing ZIP',
		'billingcontactstate' => 'Billing State',
		'billingcontactcountry' => 'Billing Country',
		'billingcontacttelnocc' => 'Billing Phone Country Code',
		'billingcontacttelno' => 'Billing Phone',
		'billingcontactemailaddr' => 'Billing Email',
		'techcontacttelno' => 'Tech Phone',
		'techcontactname' => 'Tech Name',
		'techcontactstate' => 'Tech State',
		'techcontactaddress1' => 'Tech Address',
		'techcontactaddress2' => 'Tech Street',
		'techcontactaddress3' => 'Tech Address 2',
		'techcontactcountry' => 'Tech Country',
		'techcontactemailaddr' => 'Tech Email',
		'techcontactcompany' => 'Tech Company',
		'techcontacttelnocc' => 'Tech Phone Country Code',
		'techcontactcity' => 'Tech City',
		'techcontactzip' => 'Tech ZIP',
		'admincontacttelno' => 'Admin Phone',
		'admincontactname' => 'Admin Name',
		'admincontactstate' => 'Admin Country',
		'admincontactaddress3' => 'Admin Address 2',
		'admincontactaddress2' => 'Admin Street',
		'admincontactcountry' => 'Admin Country',
		'admincontactaddress1' => 'Admin Address',
		'admincontactemailaddr' => 'Admin Email',
		'admincontactcompany' => 'Admin Company',
		'admincontacttelnocc' => 'Admin Phone Country Code',
		'admincontactcity' => 'Admin City',
		'admincontactzip' => 'Admin ZIP',
		'registrantcontacttelno' => 'Registrant Phone',
		'registrantcontactname' => 'Registrant Name',
		'registrantcontactstate' => 'Registrant State',
		'registrantcontactaddress3' => 'Registrant Address 2',
		'registrantcontactaddress2' => 'Registrant Street',
		'registrantcontactcountry' => 'Registrant Country',
		'registrantcontactaddress1' => 'Registrant Address',
		'registrantcontactemailaddr' => 'Registrant Email',
		'registrantcontactcompany' => 'Registrant Organization',
		'registrantcontacttelnocc' => 'Registrant Phone Country Code',
		'registrantcontactcity' => 'Registrant City',
		'registrantcontactzip' => 'Registrant ZIP'
		);
		
		UTIL::array_xlate_keys($info, $xlate);
	}
	
	###########################################################################

	public function whois($domain, $fallback)
	{
		try {
			$info = array();
			if($domain != "") {
				$orderid = $this->get_orderid($domain);
				if($orderid !== "" && intval($orderid) > 0) {
					$info = $this->get_orderinfo($orderid);
					if($info !== false)
					{
						if(isset($info['endtime'])) {
							$info['Registrar Registration Expiration Date'] = date("Y-M-d", $info['endtime']);
							unset($info['endtime']);
						}
						if(isset($info['creationtime'])) {
							$info['Creation Date'] = date("Y-M-d", $info['creationtime']);
							unset($info['creationtime']);
						}
						for($i = 1; $i < 8; $i++) {
							$ns = "ns{$i}";
							if(isset($info[$ns])) {
								$info["Name Server {$i}"] = $info[$ns];
								unset($info[$ns]);
							}
						}
						$ctypes = array('billingcontact','techcontact','admincontact','registrantcontact');
						foreach($ctypes as $ctype)
							$this->parse_contact_info($info, $ctype);
						$info = array_filter($info);
						$info = UTIL::array_flatten($info);
						$unsetkeys = array('entityid', 'domsecret', 'classname', 'parentkey', 'customerid',
							'techcontactparentkey', 'billingcontactparentkey', 'admincontactparentkey', 'registrantcontactparentkey',
							'billingcontactcustomerid', 'techcontactcustomerid', 'admincontactcustomerid', 'registrantcontactcustomerid',
							'billingcontactcontactid', 'techcontactcontactid', 'admincontactcontactid', 'registrantcontactcontactid',
							'billingcontactcontactstatus', 'techcontactcontactstatus', 'admincontactcontactstatus', 'registrantcontactcontactstatus',
							'billingcontacttype', 'techcontacttype', 'admincontacttype', 'registrantcontacttype',
							'recurring', 'productkey', 'productcategory', 'customercost', 'moneybackperiod', 'classkey', 'isImmediateReseller',
							'bulkwhoisoptout', 'isprivacyprotected', 'description', 'paused', 'noOfNameServers', 'isOrderSuspendedUponExpiry',
							'entitytypeid', 'multilingualflag', 'raaVerificationStatus', 'allowdeletion', 'orderSuspendedByParent', 'orderid',
							'privacyprotectedallowed'
						);
						foreach($unsetkeys as $key)
							unset($info[$key]);
						$this->cleanup_keys($info);
						$info = UTIL::array_to_text($info);
					}
					else
						throw new Exception("Unable to connect to ResellerClub API server");
				}
				else
					throw new Exception("Unable to connect to ResellerClub API server");
			}
			return $info;
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
		$count = 500;
		$page=1;
		while(1) {
			$added = 0;
			$url = $this->api_url_base . "search.json?auth-userid={$this->api_customer_id}&api-key={$this->api_access_key}&no-of-records={$count}&page-no={$page}";
			$info = UTIL::curl_get_url($url, 10);
			$info = $info['result'];
			if($info !== "") {
				$info = json_decode($info, true);
				if(isset($info['message'])) 
					throw new Exception($info['message']);
				else {
					if(is_array($info))
						ksort($info);
					foreach($info as $k=>$v) {
						if(is_array($v)) {
							if(isset($v['entity.description']) && isset($v['entitytype.entitytypename'])) {
								if(UTIL::ends_with($v['entitytype.entitytypename'], " Domain Name")) {
									$domains[] = $v['entity.description'];
									$added++;
								}
							}
						}
					}
				}
			}
			else
				throw new Exception("Unable to connect to ResellerClub API server");
			if(!$added)
				break;
			$page++;
		}
		return $domains;
	}
}

###############################################################################
	
?>