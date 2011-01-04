#!/usr/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
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
  | Authors:    Jakub Vrana <vrana@php.net>                              |
  +----------------------------------------------------------------------+

  $Id$
*/

if (!isset($_SERVER["argv"][1])) {
	echo "Purpose: Check XML syntax of single PHP manual file.\n";
	echo "Usage: xml-check.php filename.xml [xmllint-command]\n";
	echo "Note: Links are not checked.\n";
	exit();
}

$file = file_get_contents($_SERVER["argv"][1]);
$xmllint = (isset($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : "xmllint");
$root = str_replace('\\', '/', dirname(__FILE__));
$root = substr($root, 0, strrpos($root, '/'));
$realpath = str_replace('\\', '/', realpath($_SERVER["argv"][1]));
$example_filename = "$root/xml-check.xml";
$rootpos = strlen($root) + 1;
$language = substr($realpath, $rootpos, strpos($realpath, '/', $rootpos) - $rootpos);
$header = preg_replace('~.*(<!DOCTYPE.*)<book.*~s', '\\1', file_get_contents("$root/manual.xml.in"));
$header = str_replace(array('@srcdir@', '@LANGDIR@', "\n"), array('.', $language, ''), $header); // \n to preserve line numbers

$file = preg_replace('~(<?xml[^>]*>)(.*)~s', "\\1$header<book>" . (!preg_match("~<chapter|<reference|<appendix~", $file) ? "<chapter id='example'><title>Example</title>\\2\n</chapter>" : "\\2") . "\n</book>\n", $file);
$fp = fopen($example_filename, "wb");
fwrite($fp, $file);
fclose($fp);

passthru("$xmllint --noent --noout --valid $example_filename 2> $example_filename.out"); // xmllint outputs to stderr which is not catched by shell_exec, 2> &1 doesn't work on Windows
$errors = file_get_contents("$example_filename.out");
$errors = preg_replace("~.*validity error : IDREF attribute linkend references an unknown ID.*\n.*\n.*\n~", "", $errors);
$errors = str_replace($example_filename, $_SERVER["argv"][1], $errors);

if (empty($errors)) {
	echo "Success: Your file passed this XML check, do consider running 'make test' as well.\n";
} else {
	echo "Errors: The following XML error exist:\n";
	echo $errors;
}

//~ unlink("$example_filename");
unlink("$example_filename.out");
?>
