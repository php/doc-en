<?php
/*
  +----------------------------------------------------------------------+
  | PHP Documentation                                                    |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Nuno Lopes <nlopess@php.net>                             |
  +----------------------------------------------------------------------+
 
  $Id$
*/


/*
 This script updates the appendices/extensions.xml file automatically based
 on the tags placed in the 'reference.xml' files:
<!-- Purpose: xx -->
<!-- Membership: core, pecl, bundled, external -->
<!-- State: deprecated, experimental -->

		--- NOTE: PHP >= 5.2 needed ---
*/

$basedir = realpath(dirname(__FILE__) . '/..');
$files   = glob("$basedir/en/reference/*/reference.xml");
sort($files);
$Purpose = $Membership = $State = $debug = array();

// read the files and save the tags' info
foreach ($files as $filename) {

	$file = file_get_contents($filename);
	$miss = array('Purpose'=>1, 'Membership'=>1);

	// get the extension's name
	preg_match('/<reference[^>]+(?:xml:)?id=[\'"]([^\'"]+)[\'"]/S', $file, $match);
	if (empty($match[1])) {
		$debug['unknown-extension'][] = $filename;
		continue;
	} else {
		$ext = $match[1];
	}
	
	if (preg_match_all('/<!--\s*(\w+):\s*([^-]+)-->/S', $file, $matches, PREG_SET_ORDER)) {

		foreach ($matches as $match) {
			switch($match[1]) {
				case 'Purpose':
					$ext_list[$ext] = rtrim($match[2]); // for debugging purposes
				case 'State':
					${$match[1]}[rtrim($match[2])][$ext] = 1;
					unset($miss[$match[1]]); // for the debug part below
					break;

				case 'Membership':
					foreach (explode(',', $match[2]) as $m) {
						$m = trim($m);
						switch($m) {
							case 'pecl':
							case 'bundled':
							case 'external':
							case 'core':
								$Membership[$m][$ext] = 1;
								unset($miss['Membership']); // for the debug part below
								break;
							default:
								$debug['bogus-membership'][] = array($ext, $m);
						}
					}
			} //first switch
		} //first foreach
	} // if(regex)


	// debug section: let user know which extensions don't have the tags

	// if the extension is deprecated, we don't need any more info
	if (empty($State['deprecated'][$ext])) {

		// purpose not set
		if (isset($miss['Purpose'])) {
			$debug['purpose'][] = $ext;
		}

		// membership not set
		if (isset($miss['Membership'])) {
			$debug['membership'][] = $ext;
		}

	}

}


// ---------- generate the text to write -------------


$xml = file_get_contents("$basedir/en/appendices/extensions.xml");
// little hack to avoid loosing the entities
$xml = preg_replace('/&([^;]+);/', PHP_EOL.'<!--'.PHP_EOL.'entity: "$1"'.PHP_EOL.'-->'.PHP_EOL, $xml);

$simplexml = simplexml_load_string($xml);


foreach ($simplexml->children() as $node) {

	$tmp = explode('.', (string)$node->attributes('xml', true));
	$section = ucfirst($tmp[1]); // Purpose, State or Membership

	foreach ($node->children() as $topnode) {
		$tmp     = explode('.', (string)$topnode->attributes('xml', true));
		$topname = $tmp[count($tmp)-1];

		// this means that we have 2 levels (e.g. basic.*)
		if ($topnode->section->itemizedlist) {
			foreach ($topnode as $lastnode) {
				$tmp  = explode('.', (string)$lastnode->attributes('xml', true));
				$name = $tmp[1].'.'.$tmp[2];

				$lastnode->itemizedlist = PHP_EOL; // clean the list

				foreach ($Purpose[$name] as $ext => $dummy) {
					unset($ext_list[$ext]); // to generate the debug messages later

					$lastnode->itemizedlist = $lastnode->itemizedlist . <<< XML
     <listitem><para><xref linkend="$ext"/></para></listitem>

XML;
				}

				$lastnode->itemizedlist = $lastnode->itemizedlist . '    ';

			}

		} else { // just 1 level

			$tmp = $$section;

			// we can get here as a father of 2 levels childs
			if (empty($tmp[$topname])) continue;

			$topnode->itemizedlist = PHP_EOL; // clean the list

			foreach($tmp[$topname] as $ext => $dummy) {

				// to generate the debug messages later
				if ($section == 'Purpose') {
					unset($ext_list[$ext]);
				}

				$topnode->itemizedlist = $topnode->itemizedlist . <<< XML
    <listitem><para><xref linkend="$ext"/></para></listitem>

XML;
			}

			$topnode->itemizedlist = $topnode->itemizedlist . '   ';

		} //end of 1 level handling
	}
}


$xml = strtr(html_entity_decode($simplexml->asXML()), array("\r\n" => "\n", "\r" => PHP_EOL, "\n" => PHP_EOL));
// get the entities back again
$xml = preg_replace('/( *)[\r\n]*<!--\s+entity: "([^"]+)"\s+-->[\r\n]*/', '$1&$2;'.PHP_EOL.PHP_EOL, $xml);
file_put_contents("$basedir/en/appendices/extensions.xml", $xml);


// print the debug messages:
if (isset($debug['purpose'])) {
	echo "\nExtensions Missing Purpose:\n";
	print_r($debug['purpose']);
}

if (count($ext_list)) {
	echo "\nExtensions with bogus Purpose:\n";

	foreach ($ext_list as $ext => $bug) {
		echo "$ext \t => '$bug'\n";
	}
}

if (isset($debug['membership'])) {
	echo "\nExtensions Missing Membership:\n";
	print_r($debug['membership']);
}

if (isset($debug['bogus-membership'])) {
	echo "\nExtensions with bogus Membership:\n";
	print_r($debug['bogus-membership']);
}

if (isset($debug['unknown-extension'])) {
	echo "\nExtensions with unknown extension title:\n";
	print_r($debug['unknown-extension']);
}

if (empty($debug)) {
	echo "Success: Check {$basedir}/en/appendices/extensions.xml for details\n";
}


?>
