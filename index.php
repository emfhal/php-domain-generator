<?php
//set_time_limit(0);
//ini_set('memory_limit', '-1');
require("functions.php");

define("URL", (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$chars = array();
$chars[] = "*l"; //random letter;
$chars[] = "*l"; //random letter;
$chars[] = "*l"; //random letter;
$chars[] = "*l"; //random letter;
$chars[] = "*l"; //random letter;
$chars[] = "*n"; //random number;

$stop = countCharRand($chars); // The option number (of all diffrent combination char)

$tld = "com";

header('Content-Type: application/json; charset=utf-8');
echo json_encode(isDomainFree($chars, $tld, $stop, array(), array()));
die();

function isDomainFree($chars, $tld, $stop, $arr = array(), $free = array())
{
    if ($stop == 0) {
        file_put_contents(urlencode("Output Report" . date("Y-m-d H:i:s")) . ".txt", json_encode($free));
        return $free;
    }
    $rand = false;
    $i = 0;
    do {
        $domain = array();
        foreach ($chars as $c) {
            if (count(explode("*", $c)) > 1) {
                $c = charRand(explode("*", $c)[1]);
                $rand = true;
            }
            $domain[] = $c;
        }
        $domain = implode("", $domain) . "." . $tld;
    } while (in_array($domain, $arr));
    $whois = json_decode(file_get_contents(URL . "/phpWhois/index.php?mode=true&period=" . urlencode("1 days") . "&domain=" . urlencode($domain)), true);
    if (!empty($whois['regrinfo']['registered'])) {
        if ($whois['regrinfo']['registered'] == "no") {
            $free[] = $domain;
        }
    }
    $arr[] = $domain;
    $stop = $stop - 1;
    return isDomainFree($chars, $tld, $stop, $arr, $free);
}

function countCharRand($chars)
{
    $num = 1;
    foreach ($chars as $c) {
        if (count(explode("*", $c)) > 1) {
            $c = explode("*", $c)[1];
            if ($c == "l") {
                $values = 'qwertyuiopasdfghjklzxcvbnm';
                $num = $num * strlen($values);
            } elseif ($c == "n") {
                $values = '1234567890';
                $num = $num * strlen($values);
            } elseif ($c == "a") {
                $values = 'qwertyuiopasdfghjklzxcvbnm1234567890';
                $num = $num * strlen($values);
            }
        } else {
            $num = 1 * $num;
        }
    }
    return $num;
}

function charRand($c)
{
    if ($c == "l") {
        $values = 'qwertyuiopasdfghjklzxcvbnm';
        return  $values[rand(0, strlen($values) - 1)];
    } elseif ($c == "n") {
        $values = '1234567890';
        return  $values[rand(0, strlen($values) - 1)];
    } elseif ($c == "a") {
        $values = 'qwertyuiopasdfghjklzxcvbnm1234567890';
        return  $values[rand(0, strlen($values) - 1)];
    }
    return false;
}
