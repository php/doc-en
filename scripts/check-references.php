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
	echo "Purpose: Check parameters (types, optional, reference) from PHP sources.\n";
	echo "Usage: check-references.php language\n";
	echo "Notes:\n";
	echo "- Functions not found in sources are checked as without references.\n";
	echo "- Types and optional params are checked only in some functions.\n";
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
	"first_argument_force_ref" => array(1),
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

$valid_types = "int|float|string|bool|resource|array|object|mixed|number";
$operators = "!=|<=?|>=?|==";
$max_args = 12; // maximum number of regular function arguments

// convert source formatting to document types, built from ZendAPI/zend.arguments.retrieval and howto/chapter-conventions
function params_source_to_doc($type_spec)
{
	static $zend_params = array(
		"l" => "int",
		"d" => "float",
		"s" => "string",
		"b" => "bool",
		"r" => "resource",
		"a" => "array",
		"o" => "object",
		"O" => "object",
		"z" => "mixed",
		"Z" => "mixed",
		"|" => "optional"
	);
	$return = array();
	for ($i=0; $i < strlen($type_spec); $i++) {
		$ch = $type_spec{$i};
		if ($ch != "/" && $ch != "!") {
			if (!isset($zend_params[$ch])) {
				echo "! Unknown formatting specifier '$ch' in '$type_spec'.\n";
				$zend_params[$ch] = "unknown";
			}
			$return[] = $zend_params[$ch];
		}
	}
	return $return;
}

// some parameters should be passed only by reference but they are not forced to
$wrong_refs = array("dbplus_info", "dbplus_next");
$difficult_params = array(
	"ibase_blob_import",
	"imagefilter",
	"mt_rand", "rand",
	"mcrypt_get_block_size", "mcrypt_get_key_size", "mcrypt_get_cipher_name", // inverse order
	"mysql_ping",
	"pdf_get_parameter",
	"tidy_getopt", // uses zend_parse_method_parameters
	// better to fix in sources:
	"imagepstext",
	"ncurses_keyok", "ncurses_use_env", "ncurses_use_extended_names",
	"openssl_x509_export_to_file", "openssl_x509_export",
	"snmp_set_quick_print",
	"tcpwrap_check",
	"get_headers",
	"wddx_packet_end",
	"apd_echo",
	"fdf_set_on_import_javascript",
);
$difficult_arg_count = array(
	"getdate", "min", "max", "implode", "strtok", "sybase_fetch_object",
	"cpdf_text", "pdf_get_parameter", "odbc_exec", "odbc_result_all", "yaz_wait",
	// take account of multiple methodsynopsis:
	"crack_check", "ibase_prepare", "mysqli_stmt_bind_param", "pg_fetch_result", "pg_put_line", "pg_query", "pg_set_client_encoding", "strtr", "yaz_set_option",
);

// read referenced parameters from sources
$source_refs = array(); // array("function_name" => number_ref, ...)
$source_types = array(); // array("function_name" => array("type_spec", filename, lineno), ...)
$source_arg_counts = array(); // array("function_name" => array(disallowed_count => true, ...), ...)
foreach (array_merge(glob("$zend_dir/*.c*"), glob("$phpsrc_dir/ext/*/*.c*"), glob("$pecl_dir/*/*.c*")) as $filename) {
	$file = file_get_contents($filename);
	
	// references
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
	
	// read parameters
	preg_match_all('~^(?:ZEND|PHP)(?:_NAMED)?_FUNCTION\\(([^)]+)\\)(.*)^\\}~msSU', $file, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE); // }}} is not in all sources so ^} is used instead
	foreach ($matches as $val) {
		$function_name = strtolower(trim($val[1][0]));
		$lineno = substr_count(substr($file, 0, $val[2][1]), "\n") + 1;
		$function_body = $val[2][0];
		
		// types and optional
		if (!in_array($function_name, $difficult_params)
		&& strpos($function_body, 'zend_parse_parameters_ex') === false // indicate difficulty
		&& preg_match('~.*zend_parse_parameters\\([^,]*,\\s*"([^"]*)"~sS', $function_body, $matches2) // .* to catch last occurence
		// zend_parse_method_parameters is not yet supported
		) {
			$source_types[$function_name] = array($matches2[1], $filename, $lineno);
		} elseif (!in_array($function_name, $difficult_arg_count)) {
		
			// arguments count
			$zend_num_args = "ZEND_NUM_ARGS()";
			if (preg_match('~([a-zA-Z0-9_.]+)\\s*=\\s*ZEND_NUM_ARGS()~S', $function_body, $matches2)) { // int argc = ZEND_NUM_ARGS();
				$zend_num_args = $matches2[1];
			}
			$zend_num_args = preg_quote($zend_num_args, "~");
			if (preg_match("~^([ \t]+)switch\\s*\\(\\s*$zend_num_args\\s*\\)(.*)^\\1\\}~msSU", $function_body, $matches2) && preg_match('~\\bdefault\\s*:.*WRONG_PARAM_COUNT~sS', $matches2[2])) {
				$source_arg_counts[$function_name] = array(array_fill(0, $max_args+1, true), $filename, $lineno);
				$source_arg_count =& $source_arg_counts[$function_name][0];
				$switch = $matches2[2];
				preg_match_all('~\\bcase\\s+([0-9]+)\\s*:~S', $switch, $matches2);
				foreach ($matches2[1] as $val) {
					unset($source_arg_count[$val]);
				}
			} elseif (preg_match_all("~(?:([0-9]+)\\s*($operators)\\s*$zend_num_args|$zend_num_args\\s*($operators)\\s*([0-9]+))(?=[^{]+\\{[^}]+WRONG_PARAM_COUNT)~S", $function_body, $matches2, PREG_SET_ORDER)) { //! should differentiate between || and &&
				$source_arg_counts[$function_name] = array(array(), $filename, $lineno);
				$source_arg_count =& $source_arg_counts[$function_name][0];
				foreach ($matches2 as $val) {
					$number = $val[1] . $val[4];
					$operator = strtr($val[2], "><", "<>") . $val[3]; // unify to $zend_num_args $operator $number
					switch ($operator{0}) {
					case "=":
					case "!":
						if (!$source_arg_count) {
							$source_arg_count = array_fill(0, $max_args+1, true);
						}
						unset($source_arg_count[$number]);
						break;
					/* old version
					case "=":
						$source_arg_count[$number] = true;
						break;
					*/
					case "<":
						for ($i=0; $i < $number; $i++) {
							$source_arg_count[$i] = true;
						}
						break;
					case ">":
						for ($i=$number+1; $i <= $max_args; $i++) {
							$source_arg_count[$i] = true;
						}
						break;
					}
					if ($operator == "<=" || $operator == ">=") {
						$source_arg_count[$number] = true;
					}
				}
			}
		}
	}
}
echo "Sources were read.\n";

// compare with documentation
foreach (glob("$phpdoc_dir/reference/*/functions/*.xml") as $filename) {
	if (preg_match('~^(.*<methodsynopsis>(.*))<methodname>([^<]+)<(.*)</methodsynopsis>~sSU', file_get_contents($filename), $matches)) {
		$lineno = substr_count($matches[1], "\n") + 1;
		$return_type = $matches[2];
		$function_name = strtolower(trim($matches[3]));
		if (strpos($function_name, '-') || strpos($function_name, ':')) {
			continue; // methods are not supported
		}
		$methodsynopsis = $matches[4];
		
		// return type
		if (!preg_match("~<type>(void|$valid_types)</type>~", $return_type)) {
			echo "Wrong return type in $filename on line $lineno.\n";
		}
		
		// references
		$source_ref =& $source_refs[$function_name];
		preg_match_all('~<parameter>(&amp;)?~S', $methodsynopsis, $matches);
		$byref = array();
		foreach ($matches[1] as $key => $val) {
			if ($val) {
				$byref[] = $key + 1;
			}
		}
		if (!in_array($function_name, $wrong_refs) 
		&& (is_int($source_ref) ? $byref[0] != $source_ref || count($byref) != count($matches[1]) - $source_ref + 1 : $byref != $source_ref)
		) {
			echo (isset($source_ref) ? "Parameter(s) " . (is_int($source_ref) ? "$source_ref and rest" : implode(", ", $source_ref)) : "Nothing") . " should be passed by reference in $filename on line $lineno.\n";
		}
		$source_type =& $source_types[$function_name];
		$source_arg_count =& $source_arg_counts[$function_name];
		
		// parameter types and optional
		preg_match_all('~<methodparam(\\s+choice=[\'"]opt[\'"])?>\\s*<type>([^<]+)</type>\\s*<parameter>([^<]+)~i', $methodsynopsis, $matches); // (PREG_OFFSET_CAPTURE can be used to get precise line numbers)
		foreach ($matches[2] as $i => $val) {
			if (!preg_match("~callback|$valid_types~", $val)) {
				echo "Parameter #" . ($i+1) . " has wrong type '$val' in $filename on line " . ($lineno + $i + 1) . ".\n";
			}
		}
		if (isset($source_type)) {
			$optional_source = false;
			$optional_doc = false;
			$i = 0;
			$error = "";
			foreach (params_source_to_doc($source_type[0]) as $param) {
				if ($param == "optional") {
					$optional_source = true;
					continue;
				} elseif (isset($matches[2][$i])) { // sufficient number of parameters in the documentation
					if ($matches[2][$i] != $param && $param != "mixed") {
						$error .= "Parameter #" . ($i+1) . " should be of type $param (is " . $matches[2][$i] . ") in $filename on line " . ($lineno + $i + 1) . ".\n";
					}
					if (!empty($matches[1][$i])) {
						$optional_doc = true; // all rest is optional to allow e.g. exif_thumbnail(filename [, width, height [, imagetype]])
					}
					if ($optional_doc != $optional_source) {
						$error .= "Parameter #" . ($i+1) . " should" . ($optional_source ? "" : " not") . " be optional in $filename on line " . ($lineno + $i + 1) . ".\n";
					}
				}
				$i++;
			}
			if ($i != count($matches[2])) {
				$error = "Wrong number of parameters (" . count($matches[2]) . " instead of $i) in $filename on line $lineno.\n"; // other errors ignored
			}
			if ($error) {
				echo "$error: source in $source_type[1] on line $source_type[2].\n";
			}
		
		// arguments count
		} elseif (isset($source_arg_count)) {
			$disallowed = array();
			foreach ($matches[1] as $key => $val) {
				if (!$val) {
					$disallowed[$key] = true;
				}
			}
			$count = count($matches[3]);
			if (!$matches[3] || substr($matches[3][$count - 1], -3) != "...") {
				if ($count > $max_args) {
					echo "Warning: Too much parameters in $function_name.\n";
				} elseif ($count < $max_args) {
					$disallowed += array_fill($count + 1, $max_args - $count, true);
				}
			}
			if ($source_arg_count[0] != $disallowed) {
				echo "Wrong arguments count in $filename on line $lineno.\n";
				echo ": source in $source_arg_count[1] on line $source_arg_count[2].\n";
			}
		}
	}
}
echo "Done.\n";

?>
