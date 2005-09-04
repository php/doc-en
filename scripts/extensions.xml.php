<?php
/*
  +----------------------------------------------------------------------+
  | PHP Documentation                                                    |
  +----------------------------------------------------------------------+
  | Copyright (c) 2005 The PHP Group                                     |
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

		--- NOTE: PHP >= 5 needed ---
*/

$basedir = realpath(dirname(__FILE__) . '/..');
$files   = glob("$basedir/en/reference/*/reference.xml");
sort($files);
$Purpose = $Membership = $State = $debug = array();

// read the files and save the tags' info
foreach ($files as $file) {

	$tmp  = explode('/', $file, -1);
	$file = file_get_contents($file);
	$ext  = array_pop($tmp);

	$miss = array('Purpose'=>1, 'Membership'=>1);

	if (preg_match_all('/<!--\s*(\w+):\s*([^-]+)-->/S', $file, $matches, PREG_SET_ORDER)) {
		//print_r($matches);
		foreach ($matches as $match) {
			switch($match[1]) {
				case 'Purpose':
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

uksort($Purpose, sort_purpose);
ksort($Membership);
ksort($State);

// ---------- generate the text to write -------------

$write = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<!-- \$Revision$ -->

<!--
  DO NOT TRANSLATE THIS FILE! All the content that is displayed
  on the extension categorization page in your translated manual
  can be translated in extensions.ent
-->

<appendix id="extensions">
 &extcat.intro;

 <section id="extensions.purpose">
  &extcat.purpose;

XML;


// purpose
$old_toplevel = '';
$level = 0;

foreach ($Purpose as $name => $exts) {

	$tmp      = explode('.', $name);
	$toplevel = $tmp[0];

	// 1 level purpose
	if (count($tmp) == 1) {
		$old_toplevel = '';
		$write .= close_tags($level == 2 ? 3 : $level);
		$write .= <<< XML

  <section id="refs.$name">
   &extcat.purpose.$name;
   <itemizedlist>

XML;

		$level = 1;

	// 2 level purpose
	} else {

		if ($old_toplevel != $toplevel) {
			$write .= close_tags($level == 2 ? 3 : $level);
			$write .= <<< XML

  <section id="refs.$toplevel">
   &extcat.purpose.$toplevel;

XML;
		} else {
			$write .= close_tags($level);
		}

			$write .= <<< XML

   <section id="refs.$name">
    &extcat.purpose.$name;
    <itemizedlist>

XML;
		$old_toplevel = $toplevel;
		$level = 2;
	}


	foreach ($exts as $ext => $dummy) {
		$write .= indent($level, "    <listitem><para><xref linkend=\"ref.$ext\"/></para></listitem>" . PHP_EOL);
	}

	$write .= indent($level, '   </itemizedlist>' . PHP_EOL);
		//indent($level, '  </section>'.PHP_EOL);

}

$write .= close_tags($level) . ' </section>' . PHP_EOL;



///--------end of purpose
// membership

$write .= <<< XML

 <section id="extensions.state">
  &extcat.state;

XML;


foreach ($State as $type => $exts) {

	$write .= <<< XML

  <section id="extensions.state.$type">
   &extcat.state.$type;
   <itemizedlist>

XML;

	foreach ($exts as $ext => $dummy) {
		$write .= "    <listitem><para><xref linkend=\"ref.$ext\"/></para></listitem>".PHP_EOL;
	}

	$write .= <<< XML
   </itemizedlist>
  </section>

XML;

}

$write .= " </section>".PHP_EOL;



///--------end of membership
// state

$write .= <<< XML

 <section id="extensions.membership">
  &extcat.membership;

XML;


foreach ($Membership as $type => $exts) {

	$write .= <<< XML

  <section id="extensions.membership.$type">
   &extcat.membership.$type;
   <itemizedlist>

XML;

	foreach ($exts as $ext => $dummy) {
		$write .= "    <listitem><para><xref linkend=\"ref.$ext\"/></para></listitem>".PHP_EOL;
	}

	$write .= <<< XML
   </itemizedlist>
  </section>

XML;

}

$write .= " </section>".PHP_EOL;


// the end :)

$write .= <<< XML

</appendix>

<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil
sgml-default-dtd-file:"../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->

XML;

file_put_contents("$basedir/en/appendices/extensions.xml", $write);


// print the debug messages:
if (isset($debug['purpose'])) {
	echo "\nExtensions Missing Purpose:\n";
	print_r($debug['purpose']);
}

if (isset($debug['membership'])) {
	echo "\nExtensions Missing Membership:\n";
	print_r($debug['membership']);
}

if (isset($debug['bogus-membership'])) {
	echo "\nExtensions with bogus Membership:\n";
	print_r($debug['bogus-membership']);
}




function indent($i, $txt) {
	return ($i==2 ? ' ' : '') . $txt;
}


// close XML tags, based on the level
function close_tags($i) {

	if ($i == 1) {
		return <<< XML
  </section>

XML;

	} elseif ($i == 2) {
		return <<< XML
   </section>

XML;
	} elseif ($i == 3) {
		return <<< XML
   </section>

  </section>

XML;
	}
}


// use this special function to sort the purpose to put the 'xx.other' at last
function sort_purpose($a, $b) {

	if ($a == $b) return 0;
	$aa = explode('.', $a);
	$bb = explode('.', $b);

	if (count($aa) == 1 || count($bb) == 1)
		return strcmp($a, $b);

	// put .other at last
	if ($aa[1] == 'other' && $aa[0] == $bb[0]) return 1;
	if ($bb[1] == 'other' && $aa[0] == $bb[0]) return -1;

	return strcmp($a, $b);

}
?>
