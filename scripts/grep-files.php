#!/usr/bin/php -q
<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 2007-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Philip Olson <philip@php.net>                            |
  +----------------------------------------------------------------------+
 
  $Id$
*/

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	echo "This script requires a non-Windows operating system.\n";
	exit;
}

$editor = getenv('EDITOR');

if ($_SERVER['argc'] == 2 &&
	in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?')) 
	|| 
	$_SERVER['argc'] < 2) {

	echo "\nFind and edit files by grepping for a case insensitive string.\n";
	echo "I will open EDITOR if it's set, and the [language directory] is optional.\n\n";
	echo "Usage:    {$_SERVER['argv'][0]} <string> [langdir]\n";
	echo "Example:  {$_SERVER['argv'][0]} 'PHP3' en\n";
	if ($editor) {
		echo "Note:     Your EDITOR is set to: {$editor}\n\n";
	} else {
		echo "Note:     You do not have an EDITOR set so I will output a string of files\n\n";
	}
	exit;
}

$langdir = '';
if (!empty($_SERVER['argv'][2])) {
	$dir = trim($_SERVER['argv'][2]);
	if (is_dir($dir)) {
		$langdir = rtrim($dir, '/') . '/';
	} else {
		echo "INFO: The desired language directory ({$dir}) does not exist. Skipping...\n";
	}
}

$search_str	= trim($_SERVER['argv'][1]);
$output		= trim(shell_exec("grep -i -r '{$search_str}' {$langdir}* "));

if (empty($output)) {
	echo "INFO: No matches for string '{$search_str}'\n";
	exit;
}

$files = array();
foreach (explode("\n", $output) as $line) {

	$filename	= trim(substr($line, 0, strpos($line, ':')));
	$ext		= pathinfo($filename, PATHINFO_EXTENSION);

	if (($ext === 'xml' || $ext === 'ent') && $filename[0] !== '.') {
		$files[$filename] = 'open';
	}
}

if (empty($files)) {
	echo "INFO: No matches for string '{$search_str}'\n";
	exit;
}

$files_str = implode(' ', array_keys($files));

if ($editor) {
	shell_exec($editor . ' ' . $files_str);
} else {
	echo $files_str;
}
