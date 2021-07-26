<?php


function viterbi_segment($text)
{
    global $max_word_length;

    $probs = array(0);
    $lasts = array(0);

    foreach (range(1, strlen($text)) as $i) {
        $max_a = 0;
        $max_b = 0;

        foreach (range(max(0, $i - $max_word_length), $i - 1) as $j) {
            if (empty($probs[$j])) $probs[$j] = 1;
            $item = $probs[$j] * word_prob(substr($text, $j, $i - $j));
            if ($item > $max_a || $item == $max_a && $j > $max_b) {
                $max_a = $item;
                $max_b = $j;
            }
        }

        $probs[] = $max_a;
        $lasts[] = $max_b;
    }

    $words = array();
    $i = strlen($text);

    while (0 < $i) {
        $words[] = substr($text, $lasts[$i], $i - $lasts[$i]);
        $i = $lasts[$i];
    }

    return array(array_reverse($words), end($probs));
}

function word_prob($word)
{
    global $dictionary;
    global $total;

    $value = isset($dictionary[$word]) ? $dictionary[$word] : 0;
    return $value / $total;
}

function words($text)
{
    preg_match_all('/[a-z]+/', $text, $matches);
    return $matches;
}

$dictionary = array();
$max_word_length = 0;
$total = 0;

$handle = fopen("phpWhois/en_full.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $w = explode(" ", $line);
        $value = (int) $w[1];
        $dictionary[$w[0]] = $value;

        $len = strlen($w[0]);
        if ($len > $max_word_length) $max_word_length = $len;

        $total += $value;
    }
    fclose($handle);
}

function getmultiplewords($word1, $word2, &$dict)
{
    if (strlen($word1) == 0) return;
    if (binary_search($word1, $dict) && binary_search($word2, $dict)) {
        return array($word2, $word1);
    }
    $word2 = $word2 . substr($word1, 0, 1);
    $word1 = substr($word1, 1);
    getmultiplewords($word1, $word2, $dict);
}

function binary_search($elem, $array)
{
    $top = sizeof($array) - 1;
    $bot = 0;

    while ($top >= $bot) {
        $p = floor(($top + $bot) / 2);
        if ($array[$p] < $elem)
            $bot = $p + 1;
        elseif ($array[$p] > $elem)
            $top = $p - 1;
        else
            return TRUE;
    }
    return FALSE;
}




function GetBetween($var1 = "", $var2 = "", $pool)
{
    $temp1 = strpos($pool, $var1) + strlen($var1);
    $result = substr($pool, $temp1, strlen($pool));
    $dd = strpos($result, $var2);
    if ($dd == 0) {
        $dd = strlen($result);
    }

    return substr($result, 0, $dd);
}


function getwords($string)
{

    if (strpos($string, "xn--") !== false) {
        return false;
    }

    $fname = '20k.txt';
    $fp = @fopen($fname, 'r');

    // Add each line to an array
    if ($fp) {
        $array = explode("\n", fread($fp, filesize($fname)));
    }
    $string = trim(str_replace('-', '', $string));
    $pspell = array_map('trim', array_filter($array));
    $check = array();
    $words = array();

    $num = round(strlen($string) / 2, 0, PHP_ROUND_HALF_DOWN);

    for ($j = 0; $j < $num; $j++) {
        for ($i = $num; $i < strlen($string); $i++) {
            echo substr($string, $j, $i);
            echo "<br>";
            if (in_array(substr($string, $j, $i), $pspell)) {
                if (!empty($check[$j])) $check[$j]++;
                else $check[$j] = 1;
                $words[] = substr($string, $j, $i);
            }
        }
    }
    $words = array_unique($words);

    if (count($check) > 0) {
        return $words;
    } else {
        return false;
    }
}



function compress($str)
{
    $strArr = str_split($str . '0');
    $count = 0;
    $resStr = '';
    $strCheck = $strArr[0];

    foreach ($strArr as $key => $value) {
        if ($strCheck == $value) {
            $count++;
        } else {
            if ($count == 1) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1];
                $count = 1;
            } elseif ($count == 2) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $strArr[$key - 1];
                $count = 1;
            } elseif ($count == 3) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1];
                $count = 1;
            } elseif ($count == 4) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1];
                $count = 1;
            } elseif ($count == 5) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1] . $strArr[$key - 1];
                $count = 1;
            } else {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $count;
                $count = 1;
            }
        }
    }
    return $resStr;
}

function compress2($str)
{
    $strArr = str_split($str . '0');
    $count = 0;
    $resStr = '';
    $strCheck = $strArr[0];
    foreach ($strArr as $key => $value) {
        if ($strCheck == $value) {
            $count++;
        } else {
            if ($count == 1) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1];
                $count = 1;
            } elseif ($count == 2) {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $strArr[$key - 1];
                $count = 1;
            } else {
                $strCheck = $value;
                $resStr .= $strArr[$key - 1] . $count;
                $count = 1;
            }
        }
    }
    return $resStr;
}

function format($name)
{
    $arr = str_split(strtolower(trim($name)));

    $array_numbers = array();
    foreach (range(0, 9) as $number) {
        $array_numbers[] = $number;
    }
    $array_letters = array();
    foreach (range('a', 'z') as $letter) {
        $array_letters[] = $letter;
    }

    $format = "";
    for ($i = 0; $i < count($arr); $i++) {
        if (count(array_diff($array_letters, array($arr[$i]))) != count($array_letters)) {
            $format .= "L";
        } elseif (count(array_diff($array_numbers, array($arr[$i]))) != count($array_numbers)) {
            $format .= "N";
        } else {
            $format .= "-";
        }
    }


    $vletters = explode(",", "a,i,o,u,e");


    $format2 = "";
    for ($i = 0; $i < count($arr); $i++) {
        if (count(array_diff($vletters, array($arr[$i]))) != count($vletters)) {
            $format2 .= "V";
        } else {
            $format2 .= "C";
        }
    }


    $returnformat['LN']['LNLN'] =  $format;
    $returnformat['LN']['LN_SHORT'] = compress($returnformat['LN']['LNLN']);
    $returnformat['CV']['CVCV'] = $format2;
    $returnformat['CV']['CV_SHORT'] = compress($returnformat['CV']['CVCV']);


    return $returnformat;
}

function is_domain($url)
{
    $parse = parse_url($url);
    if (isset($parse['host'])) {
        $domain = $parse['host'];
    } else {
        $domain = $url;
    }

    return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $domain);
}

function get_domain($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }
    return false;
}


function is_valid_domain_name($domain_name)
{
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
        && preg_match("/^.{1,253}$/", $domain_name) //overall length check
        && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)); //length of each label
}

function domain_value($url)
{
    if (!empty($url)) {
        $domain = get_domain($url);
        $name = explode(".", $domain)[0];
        $exs = str_replace($name, "", $domain);
        $count_name = strlen($name);

        $header['countname']   = $count_name;
        $header['name']  = $name;
        $header['exs']  = $exs;

        return $header;
    } else {
        return '';
    }
}
