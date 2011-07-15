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
<!-- Membership: core, pecl, bundled, external -->
<!-- State: deprecated, experimental -->

		--- NOTE: PHP >= 5.2 needed ---
*/

$basedir = realpath(dirname(__FILE__) . '/../..');
$files   = array_merge(glob("$basedir/en/reference/*/book.xml"), glob("$basedir/en/reference/pdo_*/reference.xml"));
sort($files);
$Membership = $State = $Alphabetical = $debug = array();

// read the files and save the tags' info
foreach ($files as $filename) {

	$file = file_get_contents($filename);
	$miss = array('Membership'=>1);

	// get the extension's name
	preg_match('/<(?:reference|book)[^>]+(?:xml:)?id=[\'"]([^\'"]+)[\'"]/S', $file, $match);
	if (empty($match[1])) {
		$debug['unknown-extension'][] = $filename;
		continue;
	} else {
		$ext = $match[1];
	}
	$Alphabetical['alphabetical'][$ext] = 1;
	
	if (preg_match_all('/<!--\s*(\w+):\s*([^-]+)-->/S', $file, $matches, PREG_SET_ORDER)) {

		foreach ($matches as $match) {
			switch($match[1]) {
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
	$section = ucfirst($tmp[1]); // Alphabetical, State or Membership

	foreach (($section != 'Alphabetical' ? $node->section : array($node)) as $topnode) {
		$tmp     = explode('.', (string)$topnode->attributes('xml', true));
		$topname = $tmp[count($tmp)-1];

		$tmp = $$section;

		// we can get here as a father of 2 levels childs
		if (empty($tmp[$topname])) continue;

		$topnode->itemizedlist = PHP_EOL; // clean the list

		foreach($tmp[$topname] as $ext => $dummy) {

			if ($section != 'Alphabetical') {
				$topnode->itemizedlist = $topnode->itemizedlist . <<< XML
    <listitem><para><xref linkend="$ext"/></para></listitem>

XML;
			} else {
				$topnode->itemizedlist = $topnode->itemizedlist . <<< XML
   <listitem><simpara><xref linkend="$ext"/></simpara></listitem>

XML;
			}
		}

		$topnode->itemizedlist = $topnode->itemizedlist . ($section != 'Alphabetical' ? '   ' : '  ');

	}
}


$xml = strtr(html_entity_decode($simplexml->asXML()), array("\r\n" => PHP_EOL, "\r" => PHP_EOL, "\n" => PHP_EOL));
// get the entities back again
$xml = preg_replace('/( *)[\r\n]*<!--\s+entity: "([^"]+)"\s+-->[\r\n]*/', '$1&$2;'.PHP_EOL.PHP_EOL, $xml);
file_put_contents("$basedir/en/appendices/extensions.xml", $xml);


// print the debug messages:

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
