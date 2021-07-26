# Welcome to phpWhois Domain Generator!
The goal is to create a system for finding opportunities for free domain registration.


## Free domain in the format ***LLLLN.com*** (AXCF1.COM, AFDW3.COM,...)
The chars and tld should be modified in the index.php file as follows:

```php
$chars = array();
$chars[] = "*l"; //1 random letter;
$chars[] = "*l"; //1 random letter;
$chars[] = "*l"; //1 random letter;
$chars[] = "*l"; //1 random letter;
$chars[] = "*n"; //1 random number;
$tld = "com";
```

## Free domain in the format ***ABCLNN.com*** (ABCX11.COM, ABCY22.com,...)
```php
$chars[] = "A"; 
$chars[] = "B"; 
$chars[] = "C";
$chars[] = "*l"; //1 random letter;
$chars[] = "**n"; //2 same random number;

$tld = "com";

```

## Installation steps
1. Download whois files from [phpwhois](https://sourceforge.net/projects/phpwhois/files/latest/download) and copy to "phpWhois/src/" folder.

2. Edit the "phpWhois/index.php" file and replace the API keys you received from the domain providers (Godaddy, Dynadot, and ResellerClub"). *
It is important to check with each domain provider the restrictions of the free API before using it.

3. Edit the "index.php" files and change the $chars and $tld values.

# Credits
## PHPWhois
Mark Jeftovic <markjr@easydns.com>
David Saez Padros <david@ols.es>
Ross Golder <ross@golder.org>
Dmitry Lukashin <dmitry@lukashin.ru>

## CodePunch\Whois
Anil Kumar <akumar@codepunch.com>



#### Feature requests, and bug fixes


If you want a feature or a bug fixed, [report it via project's issues tracker](https://github.com/emfhal/phpWhois-Domain-Generator/issues). However, if it's something you can fix yourself, *fork* the project, *do* whatever it takes to resolve it, and finally submit a *pull* request. I will personally thank you, and add your name to the list of contributors.

#### Author

- **Emfhal** [http://github.com/emfhal](http://github.com/emfhal)
