#!/usr/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2004 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Jakub Vrána <vrana@php.net>                              |
  +----------------------------------------------------------------------+
*/

if (isset($_SERVER["argv"][1])) {
	$lang = $_SERVER["argv"][1];
	$scripts_dir = dirname(__FILE__);
	$phpsrc_dir = realpath("$scripts_dir/../../php-src");
	$pecl_dir = realpath("$scripts_dir/../../pecl");
	$zend_dir = realpath("$scripts_dir/../../ZendEngine2");
	$phpdoc_dir = realpath("$scripts_dir/../$lang");
}

if (!isset($_SERVER["argv"][1]) || !is_dir($phpdoc_dir)) {
	echo "Purpose: Check parameters passed by reference from PHP sources.\n";
	echo "Usage: check-references.php language\n";
	echo "Note: Functions not found in sources are not checked.\n";
	exit();
}

// various names for parameters passed by reference
// array() means list of parameters, number is position from which all parameters are passed by reference
$number_refs = array(
	"second_arg_force_ref" => array(2),
	"second_args_force_ref" => array(2),
	"second_argument_force_ref" => array(2),
	"exif_thumbnail_force_ref" => array(2, 3, 4),
	"third_and_rest_force_ref" => 3,
	"third_arg_force_ref" => array(3),
	"third_args_force_ref" => array(3),
	"third_argument_force_ref" => array(3),
	"third_arg_force_by_ref_rest" => 3,
	"second_arg_force_by_ref_rest" => 2,
	"arg3to6of6_force_ref" => array(3, 4, 5, 6),
	"second_thru_fourth_args_force_ref" => array(2, 3, 4),
	"secondandthird_args_force_ref" => array(2, 3),
	"first_arg_force_ref" => array(1),
	"first_args_force_ref" => array(1),
	"firstandsecond_args_force_ref" => array(1, 2),
	"arg2and3_force_ref" => array(2, 3),
	"first_through_third_args_force_ref" => array(1, 2, 3),
	"fourth_arg_force_ref" => array(4),
	"second_and_third_args_force_ref" => array(2, 3),
	"second_fifth_and_sixth_args_force_ref" => array(2, 5, 6),
	"first_and_second__args_force_ref" => array(1, 2),
	"third_and_fourth_args_force_ref" => array(3, 4),
	"sixth_arg_force_ref" => array(6),
	"msg_receive_args_force_ref" => array(3, 5, 8),
	"all_args_force_by_ref" => 1,
);

// some parameters should be passed only by reference but they are not forced to
$wrong_source = array("dbplus_info", "dbplus_next", "php_check_syntax", "xdiff_string_merge3", "xdiff_string_patch");

// read referenced parameters from sources
$source_refs = array();
foreach (array_merge(glob("$zend_dir/*.c*"), glob("$phpsrc_dir/ext/*/*.c*"), glob("$pecl_dir/*/*.c*")) as $filename) {
	$file = file_get_contents($filename);
	preg_match_all("~^[ \t]*(?:ZEND|PHP)_FE\\((\\w+)\\s*,\\s*(\\w+)\\s*[,)]~mS", $file, $matches, PREG_SET_ORDER);
	preg_match_all("~^[ \t]*(?:ZEND|PHP)_FALIAS\\((\\w+)\\s*,[^,]+,\\s*(\\w+)\\s*[,)]~mS", $file, $matches2, PREG_SET_ORDER);
	foreach (array_merge($matches, $matches2) as $val) {
		if ($val[2] != "NULL") {
			if (empty($number_refs[$val[2]])) {
				echo "! $val[2] from $filename is not defined.\n";
			}
			$source_refs[strtolower($val[1])] = $number_refs[$val[2]];
		}
	}
}

// compare with documentation
foreach (glob("$phpdoc_dir/reference/*/functions/*.xml") as $filename) {
	if (preg_match('~^(.*<methodsynopsis>.*)<methodname>([^<]+)<(.*)</methodsynopsis>~sSU', file_get_contents($filename), $matches)) {
		$lineno = substr_count($matches[1], "\n");
		$function_name = strtolower($matches[2]);
		if (strpos($function_name, '-') || strpos($function_name, ':')) {
			continue; // methods are not supported
		}
		$methodsynopsis = $matches[3];
		$source_ref =& $source_refs[$function_name];
		preg_match_all('~<parameter>(&amp;)?~S', $methodsynopsis, $matches);
		$byref = array();
		foreach ($matches[1] as $key => $val) {
			if ($val) {
				$byref[] = $key + 1;
			}
		}
		if (is_int($source_ref) && $byref[0] == $source_ref && count($byref) == count($matches[1]) - $source_ref + 1) {
			$byref = $source_ref;
		}
		if ($byref != $source_ref && !in_array($function_name, $wrong_source)) {
			echo (isset($source_ref) ? "Parameter(s) " . (is_int($source_ref) ? "$source_ref and rest" : implode(", ", $source_ref)) : "Nothing") . " should be passed by reference in $filename on line $lineno\n";
		}
	}
}

?>
