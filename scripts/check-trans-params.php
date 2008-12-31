#!/usr/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2009 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Philip Olson <philip@php.net>                            |
  |             Jakub Vrana <vrana@php.net>                              |
  +----------------------------------------------------------------------+
*/

if (isset($_SERVER["argv"][1])) {
	$lang = $_SERVER["argv"][1];
	$basedir = dirname(__FILE__) . "/..";
}

$output_html = false;
if (!empty($_SERVER["argv"][2])) {
	$output_html = true;
}

$current_only = false;
if (!empty($_SERVER["argv"][3])) {
	$current_only = true;
}

if (!isset($_SERVER["argv"][1]) || !is_dir("$basedir/$lang/reference")) {
	echo "Purpose: Check parameters (types, optional, reference) in translated manual.\n";
	echo "Usage:\tcheck-trans-params.php language [1=output html, 0=not] [1=current translations only, 0=all]\n";
	echo "\tDefaults for optional parameters: 0\n";
	exit(1);
}

require 'include/lib-translations.inc.php';

$cwd = getcwd();
$errors = array();
chdir("$basedir/$lang/reference/");
foreach (glob("*/functions/*.xml") as $filename) {
	$filename_en = realpath("$basedir/en/reference/$filename");
	$filename = realpath($filename); // absolute path
	if (!file_exists($filename_en)) {
		$errors['file_not_exist_en'][] = array('filename' => $filename, 'filename_en' => 'n/a', 'line' => 'n/a', 'line_en' => 'n/a');
		continue;
	}
	
	if (!is_translatable($filename_en)) {
		continue;
	}

	if ($current_only) {
		if (!is_translation_current($filename_en, $filename)) {
			continue;
		}
	}

	$file = file_get_contents($filename);
	$file_en = file_get_contents($filename_en);
	preg_match_all('~<methodsynopsis>(.*)</methodsynopsis>~sU', $file, $matches, PREG_OFFSET_CAPTURE);
	preg_match_all('~<methodsynopsis>(.*)</methodsynopsis>~sU', $file_en, $matches_en, PREG_OFFSET_CAPTURE);
	$line_no    = @(substr_count(substr($file, 0, $matches[1][1]), "\n") + 1);
	$line_no_en = @(substr_count(substr($file, 0, $matches_en[1][1]), "\n") + 1);
	if (count($matches[1]) != count($matches_en[1])) {
		$errors['methodsynopsis_count'][] = array('filename' => $filename, 'line' => $line_no, 'filename_en' => $filename_en, 'line_en' => $line_no_en);
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
			$errors['return_none'][] = array('filename' => $filename, 'line' => ($line_no + 1), 'filename_en' => $filename_en, 'line_en' => ($line_no_en +1));
		} elseif (!preg_match('~<type>' . preg_quote($match[1], '~') . '</type>\\s*<methodname>~', $methodsynopsis_en)) {
			$errors['return_invalid'][] = array('filename' => $filename, 'line' => ($line_no + 1), 'filename_en' => $filename_en, 'line_en' => ($line_no_en +1));
		}
		preg_match_all('~<methodparam.*<parameter[^>]*~U', $methodsynopsis, $match);
		preg_match_all('~<methodparam.*<parameter[^>]*~U', $methodsynopsis_en, $match_en);
		if (count($match[0]) != count($match_en[0])) {
			$errors['parameters_count'][] = array('filename' => $filename, 'line' => ($line_no + 2 + $key), 'filename_en' => $filename_en, 'line_en' => ($line_no_en + 2 + $key));
		}
		foreach ($match[0] as $key => $parameter) {
			if (!isset($match_en[0][$key])) {
				break;
			}
			if ($parameter != $match_en[0][$key]) {
				$errors['parameters_invalid'][] = array('filename' => $filename, 'line' => ($line_no + 2 + $key), 'filename_en' => $filename_en, 'line_en' => ($line_no_en + 2 + $key));
			}
		}
	}
}
chdir($cwd);

if (empty($errors)) {
	echo "ALL IS GOOD!\n";
	exit;
}

if ($output_html) {
	echo "<table cellpadding='2' cellspacing='2' border='1'>\n";
	$i = 0;
	foreach ($errors as $type => $error) {
		echo "<tr><td colspan='4'>Problem: $type</td></tr>\n";
		echo "<tr><td>Filename</td><td>Filename EN</td><td>Line</td><td>Line EN</td></tr>\n";
		foreach ($error as $id => $info) {
			$bgcolor = ($i++ & 1) ? '#eeeeee' : '#ffffff';
			echo "<tr bgcolor='$bgcolor'>\n";
			echo "<td>{$info['filename']}</td>";
			echo "<td>{$info['filename_en']}</td>";
			echo "<td>{$info['line']}</td>";
			echo "<td>{$info['line_en']}</td>";
			echo "</tr>\n";
		}
		echo "</tr>\n";
		echo "<tr><td colspan='4'><hr /></td></tr>\n";
	}
	echo "</table>";
} else {
	print_r($errors);
}
