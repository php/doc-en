#!/usr/local/bin/php -q
<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2010 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Roel Vanhout <roel@2e-systems.com>                       |
  |             Derick Rethans <derick@php.net>                          |
  +----------------------------------------------------------------------+
 
  $Id$
*/

/*
 * Script to convert (most of the) php documentation
 * from docbook to unix man format.
 * */

/*
 * Problems:
 * - examples are not shown correctly
 */

$lang = 'en';
$extension = '';
$outdir = 'man7';

set_time_limit (0);
error_reporting (E_ALL & !E_NOTICE);

if(!is_dir($lang)) {
	if(is_dir("../$lang")) {
		$lang="../$lang";
		$outdir="../$outdir";
	} else {
		die("language '$lang' not found");
	}
}

if (empty ($extension)) {
	$file = `find $lang | fgrep .xml | xargs cat`;
} else {
	$file = `cat {$lang}/functions/{$extension}.xml`;
}
#$file = str_replace("\n", '', $file);

// First get everything in <refentry></refentry> tags
preg_match_all('/<refentry.*?<\/refentry>/s', $file, $refentries);

$functions = array();
$i = 0;

foreach($refentries[0] as $refentry) {
	preg_match('/<refname>(.*)<\/refname>/s', $refentry, $matches);
	if(!empty($matches[1])) {
		$functions[$i]['name'] = $matches[1];
	} else {
		$functions[$i]['name'] = '';
	}

	preg_match('/<refpurpose>(.*)<\/refpurpose>/s', $refentry, $matches);
	if(!empty($matches[1])) {
		$functions[$i]['shortdesc'] = str_replace ("\n", ' ', $matches[1]);
	} else {
		$functions[$i]['shortdesc'] = '';
	}
	$functions[$i]['shortdesc'] = preg_replace('/\s{2,}/s', ' ', $functions[$i]['shortdesc']);
	$functions[$i]['shortdesc'] = preg_replace('/^\s*/', '', $functions[$i]['shortdesc']);
	$functions[$i]['shortdesc'] = preg_replace('/\s*$/', '', $functions[$i]['shortdesc']);

	preg_match('/<methodsynopsis>(.*)<\/methodsynopsis>/s', $refentry, $matches);
	if(!empty($matches[1])) {
		$funcprototype_all = $matches[0];
		$funcprototype = $matches[1];
	} else {
		$funcprototype_all = '';
		$funcprototype = '';
	}

	preg_match('/<methodsynopsis>.*<type>(.*?)<\/type>/s', $funcprototype_all, $matches);
	if(!empty($matches[1])) {
		$functions[$i]['prototype'] = $matches[1]. ' ';
	} else {
		$functions[$i]['prototype'] = '';
	}

	preg_match('/<methodname>(.*)<\/methodname>/s', $funcprototype, $matches);
	if(!empty($matches[1])) {
		$functions[$i]['prototype'] .= preg_replace('/<.*?>/s', '', $matches[1]);
		$functions[$i]['prototype'] .= '(';
	} else {
		$functions[$i]['prototype'] = '';
	}

	preg_match_all('/<methodparam(.*?)>.*?<\/methodparam>/s', $funcprototype, $matches);
	$first = 1;

	foreach($matches[0] as $param) {
		/* Get type and name */
		$do_var = 0;
		if (preg_match ('/<type>(.*?)<\/type>(.*?)<parameter>(.*?)<\/parameter>/s', $param, $matches)) {
			$data = $matches[1]. ' '. $matches[3];
			$do_var = 1;
		} else if (preg_match ('/<parameter>(.*?)<\/parameter>/s', $param, $matches)) {
			$data = $matches[1];
			$do_var = 1;
		} 

		if ($do_var) {
			if(preg_match('/<methodparam choice="opt">.*<\/methodparam>/s', $param)) {
				if($first != 1 ) {
					$functions[$i]['prototype'] .= ' [, ' . $data . ']';
				} else {
					$functions[$i]['prototype'] .= ' [' . $data . ']';
					$first = 0;
				}
			} else {
				if($first != 1 ) {
					$functions[$i]['prototype'] .= ', ';
				} else {
					$first = 0;
				}
				$functions[$i]['prototype'] .= $data;
			}
		}
	}
	$functions[$i]['prototype'] = preg_replace('/\n/', '', $functions[$i]['prototype']);
	$functions[$i]['prototype'] = preg_replace('/\s{2,}/s', ' ', $functions[$i]['prototype']);
	$functions[$i]['prototype'] .= ')';

	$y = 0;
	preg_match_all('/<para>.*?<\/para>/s', $refentry, $matches);
	foreach($matches[0] as $paragraph) {
		if(preg_match('/<example>/s', $paragraph)) {
			// If this paragraph has an example, do some special formatting.
			preg_match('/<title>(.*)<\/title>/s', $paragraph, $tmp);
			$functions[$i]['example'] = $tmp[1];
			$functions[$i]['example'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['example']);
			$functions[$i]['example'] = preg_replace('/<.*?>/', '', $functions[$i]['example']);
			$functions[$i]['example'] .= "\n\n";
			preg_match('/<programlisting.*?>(.*)<\/programlisting>/s', $paragraph, $tmp);

			$programlisting = $tmp[1];

			/* Remove CDATA stuff */
			$programlisting = preg_replace ("/<!\[CDATA\[\n?/", "", $programlisting);
			$programlisting = preg_replace ("/]]>\n?/", "", $programlisting);

			// Hmm, no function for this?
			$programlisting = str_replace('&lt;', '<', $programlisting);
			$programlisting = str_replace('&gt;', '>', $programlisting);
			$programlisting = str_replace('&quot;', '"', $programlisting);
			$programlisting = str_replace('&amp;', '&', $programlisting);
			$programlisting = str_replace('&nbsp;', ' ', $programlisting);
			$programlisting = str_replace('&sp;', ' ', $programlisting);
			$programlisting = str_replace('&amp;', '&', $programlisting);
			#$functions[$i]['example'] .= `echo '$programlisting' | indent -kr` . "\n\n";
			$functions[$i]['example'] .= $programlisting;
		} elseif(preg_match('/See also/s', $paragraph))  {
			$functions[$i]['seealso'] = preg_replace('/<.*?>/', '', $paragraph);
			$functions[$i]['seealso'] = preg_replace('/See also:[ ]*/', '', $functions[$i]['seealso']);
			$functions[$i]['seealso'] = preg_replace('/\s{2,}/', ' ', $functions[$i]['seealso']);
			$functions[$i]['seealso'] = preg_replace('/^\s*/', '', $functions[$i]['seealso']);
			$functions[$i]['seealso'] = preg_replace('/\./', '', $functions[$i]['seealso']);
		} else {
			// Nothing special, just put it in.
			$functions[$i]['paragraph'][$y] = preg_replace('/<.*?>/', '', $paragraph);
			$functions[$i]['paragraph'][$y] = preg_replace('/\s{2,}/', ' ', $functions[$i]['paragraph'][$y]);
		}
		$y++;
	}

	$i++;
}

/*
 * We have an array now with all the data, now write it to seperate files.
 */

if(!file_exists($outdir)) {
	umask(0000);
	mkdir($outdir, 0755);
}

foreach($functions as $function) {
	if(function_exists('gzwrite')) {
		$fp = gzopen($outdir . '/php_' . $function['name'] . '.man.gz',  'w');
	} else {
		$fp = fopen($outdir . '/php_' . $function['name'] . '.man',  'w');
	}
	/*
	$function['name']
	$function['shortdesc']
	$function['prototype']
	$function['paragraph'][$y] // Array
	$function['seealso']
	$function['example']
	*/

	$page = '.TH ' . $function['name'] . " 7  \"" . date("j F, Y") . "\" \"PHPDOC MANPAGE\" \"PHP Programmer's Manual\"\n.SH NAME\n" . 
			$function['name'] . "\n.SH SYNOPSIS\n.B " . $function['prototype'] . "\n.SH DESCRIPTION\n" . $function['shortdesc'] . ".\n";
	if(!empty($function['paragraph']) && count($function['paragraph']) > 0) {
		foreach($function['paragraph'] as $para) {
			$page .= ".PP\n";
			$page .= trim($para) . "\n";
		}
	}

	if(!empty($function['example'])) {
		$page .= ".SH \"EXAMPLE\"\n";
		$page .= $function['example'] . "\n";
	}

	if(!empty($function['seealso'])) {
		$page .= ".SH \"SEE ALSO\"\n";
		$page .= $function['seealso'] . "\n";
	}

	if(function_exists('gzwrite')) {
		gzwrite($fp, $page);
	} else {
		fwrite($fp, $page);
	}
}


?>
