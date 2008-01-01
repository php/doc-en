#!/usr/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Philip Olson <philip@php.net> (idea)                     |
  |             Jakub Vrána <vrana@php.net>                              |
  +----------------------------------------------------------------------+
*/

if (isset($_SERVER["argv"][1])) {
	$lang = $_SERVER["argv"][1];
	$basedir = dirname(__FILE__) . "/..";
}

if (!isset($_SERVER["argv"][1]) || !is_dir("$basedir/$lang/reference")) {
	echo "Purpose: Check parameters (types, optional, reference) in translated manual.\n";
	echo "Usage: check-trans-params.php language\n";
	exit(1);
}

$cwd = getcwd();
chdir("$basedir/$lang/reference/");
foreach (glob("*/functions/*.xml") as $filename) {
	$filename_en = realpath("$basedir/en/reference/$filename");
	$filename = realpath($filename); // absolute path
	if (!file_exists($filename_en)) {
		echo "File $filename does not exist in en/ tree.\n";
		continue;
	}
	$file = file_get_contents($filename);
	$file_en = file_get_contents($filename_en);
	preg_match_all('~<methodsynopsis>(.*)</methodsynopsis>~sU', $file, $matches, PREG_OFFSET_CAPTURE);
	preg_match_all('~<methodsynopsis>(.*)</methodsynopsis>~sU', $file_en, $matches_en, PREG_OFFSET_CAPTURE);
	$line_no = (substr_count(substr($file, 0, $matches[1][1]), "\n") + 1);
	$line_no_en = (substr_count(substr($file, 0, $matches_en[1][1]), "\n") + 1);
	if (count($matches[1]) != count($matches_en[1])) {
		echo "Invalid count of <methodsynopsis> in $filename on line $line_no.\n";
		echo ": source in $filename_en on line $line_no_en.\n";
	}
	foreach ($matches[1] as $key => $val) {
		if (!isset($matches_en[1][$key])) {
			break;
		}
		$line_no = (substr_count(substr($file, 0, $val[1]), "\n") + 1);
		$line_no_en = (substr_count(substr($file_en, 0, $matches_en[1][$key][1]), "\n") + 1);
		$methodsynopsis = $val[0];
		$methodsynopsis_en = $matches_en[1][$key][0];
		if (preg_match('~<replaceable>~', $methodsynopsis)) {
			// ignored
		} elseif (!preg_match('~<type>([^<]*)</type>\\s*<methodname>~', $methodsynopsis, $match)) {
			echo "Return type not found in $filename on line " . ($line_no + 1) . ".\n";
			echo ": source in $filename_en on line " . ($line_no_en + 1) . ".\n";
		} elseif (!preg_match('~<type>' . preg_quote($match[1], '~') . '</type>\\s*<methodname>~', $methodsynopsis_en)) {
			echo "Invalid return type in $filename on line " . ($line_no + 1) . ".\n";
			echo ": source in $filename_en on line " . ($line_no_en + 1) . ".\n";
		}
		preg_match_all('~<methodparam.*<parameter[^>]*~U', $methodsynopsis, $match);
		preg_match_all('~<methodparam.*<parameter[^>]*~U', $methodsynopsis_en, $match_en);
		if (count($match[0]) != count($match_en[0])) {
			echo "Invalid number of parameters in $filename on line " . ($line_no + 2 + $key) . ".\n";
			echo ": source in $filename_en on line " . ($line_no_en + 2 + $key) . ".\n";
		}
		foreach ($match[0] as $key => $parameter) {
			if (!isset($match_en[0][$key])) {
				break;
			}
			if ($parameter != $match_en[0][$key]) {
				echo "Invalid parameter type/opt/reference in $filename on line " . ($line_no + 2 + $key) . ".\n";
				echo ": source in $filename_en on line " . ($line_no_en + 2 + $key) . ".\n";
			}
		}
	}
}
chdir($cwd);
