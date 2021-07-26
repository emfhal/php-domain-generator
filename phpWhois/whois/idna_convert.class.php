<?php

###############################################################################
# base.php
#
# base.php is a library of helper functions for a number of common tasks.
# Many functions are from
#
# @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
# @link   http://github.com/brandonwamboldt/utilphp/
#
# @author Anil Kumar <akumar@codepunch.com>
# @link   https://codepunch.com
#
############################################################################### 

namespace CodePunch\Base;

###############################################################################

class Util {
	
	###########################################################################
	# Start : # @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
	###########################################################################

	public static function str_to_bool($string, $default = false)
	{
		$yes_words = 'affirmative|all right|aye|indubitably|most assuredly|ok|of course|okay|sure thing|y|yes+|yea|yep|sure|yeah|true|t|on|1|oui|vrai';
		$no_words = 'no*|no way|nope|nah|na|never|absolutely not|by no means|negative|never ever|false|f|off|0|non|faux';

		if (preg_match('/^(' . $yes_words . ')$/i', $string)) {
			return true;
		} elseif (preg_match('/^(' . $no_words . ')$/i', $string)) {
			return false;
		}
		return $default;
	}

	###########################################################################
		
	public static function starts_with($string, $starts_with)
	{
		return strpos($string, $starts_with) === 0;
	}

	###########################################################################

	public static function ends_with($string, $ends_with)
	{
		return substr($string, -strlen($ends_with)) === $ends_with;
	}

	###########################################################################

	public static function str_contains($haystack, $needle)
	{
		return strpos($haystack, $needle) !== false;
	}
	
	###########################################################################
	
	public static function strip_space($string)
    {
        return preg_replace('/\s+/', '', $string);
    }
	
	###########################################################################
	
	public static function zero_pad($number, $length)
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }

	###########################################################################

	public static function get_file_ext($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	###########################################################################
    # Truncate a string to a specified length without cutting a word off.
    #
    # @param   string  $string  The string to truncate
    # @param   integer $length  The length to truncate the string to
    # @param   string  $append  Text to append to the string IF it gets
    #                           truncated, defaults to '...'
    # @return  string
    public static function safe_truncate($string, $length, $append = '...')
    {
        $ret        = substr($string, 0, $length);
        $last_space = strrpos($ret, ' ');

        if ($last_space !== false && $string != $ret) {
            $ret     = substr($ret, 0, $last_space);
        }

        if ($ret != $string) {
            $ret .= $append;
        }

        return $ret;
    }
	
	###########################################################################
    # Truncate the string to given length of charactes.
    #
    # @param $string
    # @param $limit
    # @param string $append
    # @return string
    public static function limit_characters($string, $limit = 100, $append = '...')
    {
        if (mb_strlen($string) <= $limit) {
            return $string;
        }

        return rtrim(mb_substr($string, 0, $limit, 'UTF-8')) . $append;
    }

    ###########################################################################
    # Truncate the string to given length of words.
    #
    # @param $string
    # @param $limit
    # @param string $append
    # @return string
    public static function limit_words($string, $limit = 100, $append = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $limit . '}/u', $string, $matches);

        if (!isset($matches[0]) || strlen($string) === strlen($matches[0])) {
            return $string;
        }

        return rtrim($matches[0]).$append;
    }
	
	###########################################################################
	
	public static function is_https()
    {
        return isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
    }
	
	###########################################################################
    # Returns the IP address of the client.
    #
    # @param   boolean $trust_proxy_headers Whether or not to trust the
    #                                       proxy headers HTTP_CLIENT_IP
    #                                       and HTTP_X_FORWARDED_FOR. ONLY
    #                                       use if your server is behind a
    #                                       proxy that sets these values
    # @return  string
    public static function get_client_ip($trust_proxy_headers = false)
    {
        if (!$trust_proxy_headers) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
	
	###########################################################################
    # Turns all of the links in a string into HTML links.
    #
    # Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
    #
    # @param  string $text The string to parse
    # @return string
    public static function linkify($text)
    {
        $text = preg_replace('/&apos;/', '&#39;', $text); // IE does not handle &apos; entity!
        $section_html_pattern = '%# Rev:20100913_0900 github.com/jmrware/LinkifyURL
            # Section text into HTML <A> tags  and everything else.
              (                             # $1: Everything not HTML <A> tag.
                [^<]+(?:(?!<a\b)<[^<]*)*     # non A tag stuff starting with non-"<".
              |      (?:(?!<a\b)<[^<]*)+     # non A tag stuff starting with "<".
             )                              # End $1.
            | (                             # $2: HTML <A...>...</A> tag.
                <a\b[^>]*>                   # <A...> opening tag.
                [^<]*(?:(?!</a\b)<[^<]*)*    # A tag contents.
                </a\s*>                      # </A> closing tag.
             )                              # End $2:
            %ix';

        return preg_replace_callback($section_html_pattern, array(__CLASS__, 'linkifyCallback'), $text);
    }

    ###########################################################################
    # Callback for the preg_replace in the linkify() method.
    #
    # Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
    #
    # @param  array  $matches Matches from the preg_ function
    # @return string
    protected static function linkifyRegex($text)
    {
        $url_pattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
            # Match http & ftp URL that is not already linkified.
            # Alternative 1: URL delimited by (parentheses).
            (\() # $1 "(" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $2: URL.
            (\)) # $3: ")" end delimiter.
            | # Alternative 2: URL delimited by [square brackets].
            (\[) # $4: "[" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $5: URL.
            (\]) # $6: "]" end delimiter.
            | # Alternative 3: URL delimited by {curly braces}.
            (\{) # $7: "{" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $8: URL.
            (\}) # $9: "}" end delimiter.
            | # Alternative 4: URL delimited by <angle brackets>.
            (<|&(?:lt|\#60|\#x3c);) # $10: "<" start delimiter (or HTML entity).
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $11: URL.
            (>|&(?:gt|\#62|\#x3e);) # $12: ">" end delimiter (or HTML entity).
            | # Alternative 5: URL not delimited by (), [], {} or <>.
            (# $13: Prefix proving URL not already linked.
            (?: ^ # Can be a beginning of line or string, or
            | [^=\s\'"\]] # a non-"=", non-quote, non-"]", followed by
           ) \s*[\'"]? # optional whitespace and optional quote;
            | [^=\s]\s+ # or... a non-equals sign followed by whitespace.
           ) # End $13. Non-prelinkified-proof prefix.
            (\b # $14: Other non-delimited URL.
            (?:ht|f)tps?:\/\/ # Required literal http, https, ftp or ftps prefix.
            [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
            (?: # Either on a "&" or at the end of URI.
            (?! # Allow a "&" char only if not start of an...
            &(?:gt|\#0*62|\#x0*3e); # HTML ">" entity, or
            | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
            [.!&\',:?;]? # followed by optional punctuation then
            (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$) # a non-URI char or EOS.
           ) & # If neg-assertion true, match "&" (special).
            [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
           )* # Unroll-the-loop (special normal*)*.
            [a-z0-9\-_~$()*+=\/#[\]@%] # Last char can\'t be [.!&\',;:?]
           ) # End $14. Other non-delimited URL.
            /imx';

        $url_replace = '$1$4$7$10$13<a href="$2$5$8$11$14">$2$5$8$11$14</a>$3$6$9$12';

        return preg_replace($url_pattern, $url_replace, $text);
    }

    ###########################################################################
    # Callback for the preg_replace in the linkify() method.
    #
    # Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
    #
    # @param  array  $matches Matches from the preg_ function
    # @return string
    protected static function linkifyCallback($matches)
    {
        if (isset($matches[2])) {
            return $matches[2];
        }

        return self::linkifyRegex($matches[1]);
    }
	
	###########################################################################
    # Generates a string of random characters.
    #
    # @throws  LengthException  If $length is bigger than the available
    #                           character pool and $no_duplicate_chars is
    #                           enabled
    #
    # @param   integer $length             The length of the string to
    #                                      generate
    # @param   boolean $human_friendly     Whether or not to make the
    #                                      string human friendly by
    #                                      removing characters that can be
    #                                      confused with other characters (
    #                                      O and 0, l and 1, etc)
    # @param   boolean $include_symbols    Whether or not to include
    #                                      symbols in the string. Can not
    #                                      be enabled if $human_friendly is
    #                                      true
    # @param   boolean $no_duplicate_chars Whether or not to only use
    #                                      characters once in the string.
    # @return  string
    public static function random_string($length = 16, $human_friendly = true, $include_symbols = false, $no_duplicate_chars = false)
    {
        $nice_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefhjkmnprstuvwxyz23456789';
        $all_an     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $symbols    = '!@#$%^&*()~_-=+{}[]|:;<>,.?/"\'\\`';
        $string     = '';

        // Determine the pool of available characters based on the given parameters
        if ($human_friendly) {
            $pool = $nice_chars;
        } else {
            $pool = $all_an;

            if ($include_symbols) {
                $pool .= $symbols;
            }
        }

        if (!$no_duplicate_chars) {
            return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
        }

        // Don't allow duplicate letters to be disabled if the length is
        // longer than the available characters
        if ($no_duplicate_chars && strlen($pool) < $length) {
            throw new \LengthException('$length exceeds the size of the pool and $no_duplicate_chars is enabled');
        }

        // Convert the pool of characters into an array of characters and
        // shuffle the array
        $pool       = str_split($pool);
        $poolLength = count($pool);
        $rand       = mt_rand(0, $poolLength - 1);

        // Generate our string
        for ($i = 0; $i < $length; $i++) {
            $string .= $pool[$rand];

            // Remove the character from the array to avoid duplicates
            array_splice($pool, $rand, 1);

            // Generate a new number
            if (($poolLength - 2 - $i) > 0) {
                $rand = mt_rand(0, $poolLength - 2 - $i);
            } else {
                $rand = 0;
            }
        }

        return $string;
    }

    ###########################################################################
    # Generate secure random string of given length
    # If 'openssl_random_pseudo_bytes' is not available
    # then generate random string using default function
    #
    # Part of the Laravel Project <https://github.com/laravel/laravel>
    #
    # @param int $length length of string
    # @return bool
    public static function secure_random_string($length = 16)
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);

            if ($bytes === false) {
                throw new \LengthException('$length is not accurate, unable to generate random string');
            }

            return substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $length);
        }

        // @codeCoverageIgnoreStart
        return static::random_string($length);
        // @codeCoverageIgnoreEnd
    }
	
	###########################################################################
    # Transmit UTF-8 content headers if the headers haven't already been sent.
    #
    # @param  string  $content_type The content type to send out
    # @return boolean
    public static function utf8_headers($content_type = 'text/html')
    {
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            header('Content-type: ' . $content_type . '; charset=utf-8');

            return true;
        }

        return false;
        // @codeCoverageIgnoreEnd
    }

    ###########################################################################
    # Transmit headers that force a browser to display the download file
    # dialog. Cross browser compatible. Only fires if headers have not
    # already been sent.
    #
    # @param string $filename The name of the filename to display to
    #                         browsers
    # @param string $content  The content to output for the download.
    #                         If you don't specify this, just the
    #                         headers will be sent
    # @return boolean
    public static function force_download($filename, $content = false)
    {
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            // Required for some browsers
            if (ini_get('zlib.output_compression')) {
                @ini_set('zlib.output_compression', 'Off');
            }

            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

            // Required for certain browsers
            header('Cache-Control: private', false);

            header('Content-Disposition: attachment; filename="' . basename(str_replace('"', '', $filename)) . '";');
            header('Content-Type: application/force-download');
            header('Content-Transfer-Encoding: binary');

            if ($content) {
                header('Content-Length: ' . strlen($content));
            }

            ob_clean();
            flush();

            if ($content) {
                echo $content;
            }

            return true;
        }

        return false;
        // @codeCoverageIgnoreEnd
    }

    ###########################################################################
    # Sets the headers to prevent caching for the different browsers.
    #
    # Different browsers support different nocache headers, so several
    # headers must be sent so that all of them get the point that no
    # caching should occur
    #
    # @return boolean
    public static function nocache_headers()
    {
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');

            return true;
        }

        return false;
        // @codeCoverageIgnoreEnd
    }

	###########################################################################
	# End : # @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
	###########################################################################

	public static function is_cli()
	{
		if( defined('STDIN') || 
			(empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) 
			&& count($_SERVER['argv']) > 0))
			return true;
		return false;
	}

	###########################################################################

	public static function in_array_casei($needle, $haystack) 
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack));
	}
	
	###########################################################################
	
	public static function array_flatten($array, $old_key=null) 
	{
		$return = array();
		foreach ($array as $key => $value) 
		{
			if($old_key)
				$key = "{$old_key}{$key}";
			if(is_array($value)) 
				$return = array_merge($return, self::array_flatten($value, $key));
			else 
				$return[$key] = $value;
		}
		return $return;
	}
	
	###########################################################################
	
	public static function array_xlate_keys(&$array, $xlate_table)
	{
		foreach($array as $key=>$value)
		{
			foreach($xlate_table as $xk=>$xv)
			{
				if($xk == $key)
				{
					$array[$xv] = $value;
					unset($array[$key]);
					break;
				}
			}
		}
		return $array;
	}

	###########################################################################

	public static function get_string_between($string, $start, $end, $includetokens=false)
	{
		$string = " ".$string;
		$ini = mb_stripos($string,$start);
		if ($ini == 0) return "";
		if(!$includetokens)
			$ini += mb_strlen($start);
		$len = mb_stripos($string,$end,$ini) - $ini;
		if($includetokens)
			$len += strlen($end);
		return mb_substr($string,$ini,$len);
	}

	###########################################################################

	public static function get_date_difference($pdate, $pivot=null)
	{
		if($pivot == null)
			$pivot = time(); 
		$datediff = $pdate - $pivot;
		return floor($datediff/(60*60*24));
	}

	###########################################################################

	public static function is_a_date( $str ) 
	{ 
		$stamp = strtotime( $str ); 
		if (!is_numeric($stamp)) 
			return FALSE; 
		$month = date( 'm', $stamp ); 
		$day   = date( 'd', $stamp ); 
		$year  = date( 'Y', $stamp ); 
		if (checkdate($month, $day, $year)) 
			return TRUE; 
		return FALSE; 
	}
	
	###########################################################################
	# Echo all the variables passed 
	public static function smart_echo_multiple()
	{
		$msg = "";
		$sep = ", ";
		$args = func_get_args();
		foreach($args as $a)
			$msg .= $a . $sep;
		self::smart_echo($msg);
	}
	
	###########################################################################
	
	public static function text_echo($msg)
	{
		echo "<pre>"; 
		echo $msg;
		echo "</pre>";
	}

	###########################################################################

	public static function smart_echo($msg, $prompt="")
	{
		if($prompt != "")
			$msg = "$prompt: $msg";
		if($msg === false)
			$msg = "False";
		else if($msg === true)
			$msg = "True";
		if(self::is_cli())
			echo "$msg\n";
		else
			echo "<p>$msg</p>";
	}
	
	###########################################################################

	public static function print_data($pdata)
	{
		if(is_array($pdata) || is_object($pdata))
		{
			if(!self::is_cli())
				echo "<pre>"; 
			print_r($pdata); 
			if(!self::is_cli())
				echo "</pre>";
		}
		else
			self::smart_echo($pdata);
	}

	###########################################################################
	
	public static function parse_command_line(&$parray)
	{
		if(self::is_cli())
		{
			global $argv;
			parse_str(implode('&', array_slice($argv, 1)), $parray);
			return true;
		}
		return false;
	}
	
	###############################################################################

	public static function find_all_matched_files($folder, $filemask)
	{
		$filenames = array();
		$matches = glob($folder . $filemask);
		if($matches !== false)
		{
			if(is_array($matches))
			{
				if(count($matches) > 0)
				{
					foreach($matches as $match)
						$filenames[] = basename($match);
					return $filenames;
				}
			}
		}
		return false;
	}

	###############################################################################
	# Returns the root path
	# Examples
	# https://www.example.com/wmdsed3/ => /wmdsed3/
	# https://www.example.com/scripts/wmdsed3/ => /scripts/wmdsed3/
	public static function get_install_url_path()
	{
		if(!isset($_SERVER['PHP_SELF']) || !isset($_SERVER['SCRIPT_NAME']))
			return false;

		$basepath = $_SERVER['PHP_SELF'];
		$pos = strpos($basepath, "/lib/php/");
		if($pos !== false)
			$basepath = substr($basepath, 0, $pos) . "/";
		$basepath = str_replace(basename($_SERVER['SCRIPT_NAME']), "", $basepath);
		return $basepath;
	}
	
	###############################################################################

	public static function get_root_url($default="http://your/install/path")
	{
		if(isset($_SERVER['HTTP_HOST']))
			$rooturl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
		else
			$rooturl = $default;
		$rooturl .= self::get_install_url_path();
		return $rooturl;
	}
	
	###############################################################################
	
	public static function get_install_folder_path()
	{
		global $cfg_base_folder_path;
		if(isset($cfg_base_folder_path))
			return $cfg_base_folder_path;
		
		return realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "../../../../") . DIRECTORY_SEPARATOR;
	}
	
	###############################################################################

	public static function get_local_php_log_filename()
	{
		$logfile = self::get_install_folder_path() . "logs/php.log";
		if(is_writable(dirname($logfile))) 
			return $logfile;
		return false;
	}
	
	###############################################################################

	public static function local_php_log_to_file($filename, $msg)
	{ 
		$fd = fopen($filename, "a");
		fwrite($fd, $msg);
		fclose($fd);
	}

	###############################################################################

	public static function local_php_log_truncate($length=0)
	{
		$fd = fopen(self::get_local_php_log_filename(), "r+");
		ftruncate($fd, $length);
		fclose($fd);
	}

	###############################################################################

	public static function local_php_log($msg)
	{ 
		self::local_php_log_to_file(self::get_local_php_log_filename(), $msg);
	}
	
	###############################################################################

	public static function local_php_log_request_data()
	{
		$timestamp = strftime("%c",time());
		$ldata = "\n--" . $timestamp . "--\n";
		foreach ($_REQUEST as $key=>$value) 
		{
			$ldata .= $key . " - " . $value . "\n";
		}
		self::local_php_log($ldata);
	}
	
	###########################################################################
	
	public static function get_from_array(&$entry, $default=null)
	{
		if(isset($entry))
			return $entry;
		else
			return $default;
	}
	
	###############################################################################
	
	public static function get_request_data($name, $default)
	{
		if(isset($_REQUEST[$name]))
			return $_REQUEST[$name];
		return $default;
	}
	
	###############################################################################
	
	public static function get_request_string($name, $default="")
	{
		if(isset($_REQUEST[$name]))
			return filter_var($_REQUEST[$name], FILTER_SANITIZE_STRING);
		return $default;
	}
	
	###############################################################################
	
	public static function get_numeric_request_data($name, $default)
	{
		if(isset($_REQUEST[$name]))
			return intval($_REQUEST[$name]);
		return $default;
	}
	
	###############################################################################
	# Get a request data if it is in a pre-defined list.
	static function get_predefined_request_data($name, $default, $indexarray)
	{
		$entry = $default;
		if(isset($_REQUEST[$name]))
			$entry = $_REQUEST[$name];
		if(!self::in_array_casei($entry, $indexarray))
			$entry = $default;
		return $entry;
	}
	
	###########################################################################
	
	public static function curl_get_url($url, $timeout=10, $httpauth=false, $httpheader=false)
	{
		$retv = array('result' => "", 'status' => 0);
		if(function_exists('curl_version'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			
			if($httpauth !== false)
				curl_setopt($ch, CURLOPT_HTTPAUTH, $httpauth);
			if($httpheader !== false)
				curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
			
			$retv['result'] = curl_exec ($ch);
			$retv['result'] = ($retv['result'] === false) ? "" : $retv['result'];
			$retv['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close ($ch);
		}
		return $retv;
	}
	
	###########################################################################
	
	public function whois($query, $server, $port=43, $timeout=20)
	{
		$output = "Error: Unable to connect to " . $server;
		if(($ns = @fsockopen($server, $port, $errno, $errstr, $timeout)) == true)
		{
			$output = "";
			fputs($ns,"$query\r\n");
			while(!feof($ns)) 
				$output .= fgets($ns,128); 
			fclose($ns);
		}
		return $output;
	}
	
	###########################################################################
	
	public static function array_to_text($whoisdata)
	{
		$rawwhois = "";
		if($whoisdata != "" && is_array($whoisdata))
		{
			foreach($whoisdata as $k=>$v)
			{
				$rawwhois .= "$k: ";
				if(is_array($v))
					$rawwhois .= print_r(implode("\n", $v), true);
				else
					$rawwhois .= $v;
				$rawwhois .= "\n";
			}
		}
		return trim($rawwhois);
	}
	
	###############################################################################

	public static function create_containing_folders($path) 
	{
		if (is_dir($path)) return true;
		$prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
		$return = self::create_containing_folders($prev_path);
		return ($return && is_writable($prev_path)) ? mkdir($path) : false;
	}
		
	###############################################################################
}

?>
