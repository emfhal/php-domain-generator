<?php
###############################################################################

# GoDaddy API Settings
$gd_api_access = array("xxxxxxxxxxxx");
$gd_api_secret = array("xxxxxxxxxxxx");    
$rnd = rand(0,count($gd_api_access)-1);
$gd_api_access_key 		= $gd_api_access[$rnd];
$gd_api_secret_key 		= $gd_api_secret[$rnd];

# Dynadot API Settings
$dn_api_access_key 		= 'xxxxxxxxxxxx';
//https://www.dynadot.com/account/domain/setting/api.html

# ResellerClub API Settings
$rsclub_api_access_key 	= 'xxxxxxxxxxxx';
$rsclub_customer_id    	= '1111111';

###############################################################################

require_once('src/whois.main.php');
require_once('whois/whoapi.php');

$query = !empty($_GET['domain']) ? urldecode($_GET['domain']) : "";
$period = !empty($_GET['period']) ? urldecode($_GET['period']) : "1 days";
$mode = !empty($_GET['mode']) ? true : false;

echo json_encode(Whois($query,$period,$mode));

function Whois($query,$period,$mode)
{
    if (validDomain($query) == true) {

        $path = "fetch/" . urlencode($query) . ".txt";
        if (!is_dir(dirname($path))) mkdir(dirname($path));
        $triger = false;
        if (file_exists($path)) {
            if (filemtime($path) > strtotime("-".$period)) { //adken im akovets gadol mi 1 yamim
                $triger = true;
            }
        }

        if ($triger == false) {
            $result = (new Whois())->Lookup($query, false);
            $result['api_whois'] = better($result['rawdata']);
            if (!empty($result['source']) and empty($mode)) $result['api_whois'] = better(api_whois($query, $result['source']));
            if(!empty($result['api_whois']['Error'])) $result['api_whois'] = better($result['rawdata']);
            unset($result['rawdata']);
            if (!empty($result)) file_put_contents($path, json_encode($result));
        } else {
            $result = json_decode(file_get_contents($path), true);
        }


        return $result;
    }
    return false;
}

function validDomain($domain)
{
    if (preg_match('~^[a-z0-9][a-z0-9\.-]*\.[a-z]+$~i', $domain)) return true;
    return false;
}

function better($arr)
{
    $newarr = array();
    if (!is_array($arr)) $arr = explode(PHP_EOL, $arr);

    foreach ($arr as $a) {
        $b = explode(": ", str_replace(array(">>>", "<<<"), "", $a));
        if (count($b) > 1 and !empty($b[1])) {
            $newarr[trim($b[0])] = trim($b[1]);
            if (strpos($b[0], 'Last update of WHOIS database') !== false) break;
        }
        
    }
    return $newarr;
}
