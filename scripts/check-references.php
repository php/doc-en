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
	echo "Purpose: Check parameters (types, optional, reference, count) and return types.\n";
	echo "Usage: check-references.php language [path_to_extension]\n";
	echo "Notes:\n";
	echo "- Compares documentation with PHP sources (Zend, extensions, PECL, SAPI).\n";
	echo "- Functions not found in sources are checked as without references.\n";
	echo "- Types and optional params are checked only in some functions.\n";
	exit();
}

$extension = $_SERVER["argv"][2];

// various names for parameters passed by reference
// array() means list of parameters, number is position from which all parameters are passed by reference
$number_refs = array(
	"first_arg_force_ref" => array(1),
	"second_arg_force_ref" => array(2),
	"second_args_force_ref" => array(2),
	"third_arg_force_ref" => array(3),
	"fourth_arg_force_ref" => array(4),
	"fifth_arg_force_ref" => array(5),
	"all_args_by_ref" => 1,
);

$valid_types = "int|float|string|bool|resource|array|object|mixed|number";
$invalid_types = "integer|long|double|boolean|class"; // objects are written as appropriate class name so there is no complete list of valid types
$retval_mapping = array("TRUE" => "bool", "BOOL" => "bool", "LONG" => "int", "DOUBLE" => "float", "STRING" => "string", "STRINGL" => "string", "TEXT" => "string", "TEXTL" => "string", "UNICODE" => "unicode", "UNICODEL" => "unicode", "ASCII_STRING" => "unicode", "ASCII_STRINGL" => "unicode", "ARRAY" => "array", "OBJECT" => "object", "RESOURCE" => "resource", "ZVAL" => "mixed"); // FALSE and NULL omitted because they are used for errors
$retval_types = implode('|', array_keys($retval_mapping));
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
		"t" => "string",
		"u" => "unicode",
		"C" => "class",
		"h" => "array",
		"U" => "unicode",
		"S" => "string",
		"f" => "callback",
		"x" => "string",
		"T" => "unicode",
		
		"|" => "optional"
	);
	$return = array();
	for ($i=0; $i < strlen($type_spec); $i++) {
		$ch = $type_spec[$i];
		if ($ch != "/" && $ch != "!" && $ch != "&" && $ch != "^" && $ch != "*" && $ch != "+") {
			if (!isset($zend_params[$ch])) {
				echo "! Unknown formatting specifier '$ch' in '$type_spec'.\n";
				$zend_params[$ch] = "unknown";
			}
			$return[] = $zend_params[$ch];
		}
	}
	return $return;
}

// expand macros defined in $GLOBALS['macros'] (callback for preg_replace_callback)
function expand_macros($matches)
{
	$macro = $GLOBALS['macros'][$matches[1]];
	if ($matches[2]) {
		$params = explode(",", trim($matches[2], '()'), count($macro[1]));
		return str_replace($macro[1], $params, $macro[0]);
	}
	return $macro[0];
}

// some parameters should be passed only by reference but they are not forced to
$wrong_refs = array(
	"dbplus_curr", "dbplus_first", "dbplus_info", "dbplus_last", "dbplus_next", "dbplus_prev", "dbplus_tremove",
	"php_check_syntax",
	"pdostatement::bindcolumn", "pdostatement::bindparam",
	"preg_replace", "preg_replace_callback",
	"soapclient::__soapcall",
);

$difficult_retvals = array(
	"set_error_handler", "set_exception_handler", "highlight_file", "highlight_string", "pg_cancel_query", "pg_connection_busy", "mysqli_query",
	"mb_send_mail",
	// better to fix in sources:
	"debug_print_backtrace", // array instead of void
	"dbmopen", // int instead of resource
	"pg_lo_open", // int instead of resource
	"ircg_pconnect", // int instead of resource
	"notes_search", // RETURN_LONG commented out
	"exif_tagname", // RETURN_BOOL(FALSE) instead of RETURN_FALSE
);

$difficult_params = array(
	"ibase_blob_import", "ibase_execute",
	"imagefilter",
	"maxdb_stmt_bind_result",
	"mt_rand", "rand",
	"mcrypt_get_block_size", "mcrypt_get_key_size", "mcrypt_get_cipher_name", // inverse order
	"pdf_get_parameter",
	"tidy_get_opt_doc", "tidy_getopt",
	// better to fix in sources:
	"ncurses_keyok", "ncurses_use_env", "ncurses_use_extended_names",
	"openssl_x509_export_to_file", "openssl_x509_export",
	"snmp_set_quick_print",
	"apd_echo",
	"easter_date",
	"tidy_repair_string",
);

$difficult_arg_count = array(
	"getdate", "min", "max", "implode", "strtok", "sybase_fetch_object",
	"cpdf_text", "pdf_get_parameter", "pg_fetch_assoc", "odbc_exec", "odbc_result_all", "yaz_wait",
	// take account of multiple methodsynopsis:
	"crack_check", "ibase_prepare", "maxdb_stmt_bind_param", "mysqli_stmt_bind_param", "strtr", "yaz_set_option",
	"pg_fetch_result", "pg_put_line", "pg_query", "pg_set_client_encoding", "pg_execute", "pg_query_params", "pg_prepare", "pg_set_error_verbosity",
	// better to fix in sources:
	"ora_error", "ora_errorcode",
);

// read referenced parameters from sources
$source_refs = array(); // array("function_name" => number_ref, ...)
$source_types = array(); // array("function_name" => array("type_spec", filename, lineno), ...)
$return_types = array(); // array("function_name" => array("doc_type", filename, lineno), ...)
$source_arg_counts = array(); // array("function_name" => array(disallowed_count => true, ...), ...)
foreach ((isset($extension) ? array($extension) : array_merge(array($zend_dir), glob("$phpsrc_dir/ext/*", GLOB_ONLYDIR), glob("$pecl_dir/*", GLOB_ONLYDIR), glob("$phpsrc_dir/sapi/*", GLOB_ONLYDIR))) as $dirname) {
	if (dirname($dirname) == $pecl_dir && !file_exists("$phpdoc_dir/reference/" . strtolower(basename($dirname)))) {
		continue; // skip undocumented PECL extensions
	}
	$files = array();
	$aliases = array(); // php_function => sources_function
	$macros = array(); // MACRO => array(body, array(params))
	$largedir = ($dirname == $zend_dir || $dirname == "$phpsrc_dir/ext/standard");
	$local_refs = array();
	foreach (array_merge((array) glob("$dirname/*.h"), (array) glob("$dirname/*.c*")) as $filename) {
		$file = file_get_contents($filename);
		// macros
		if (!$largedir) {
			preg_match_all("~^#define[ \t]+(\\w+)(\\([^)]+\\))?([ \t]+.+[^\\\\])\$~msU", $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $val) {
				$params = preg_split('~,\\s*~', trim($val[2], '()'));
				$macros[$val[1]] = array(trim(str_replace(array("\r", "\\\n"), "", $val[3])), $params);
			}
		}
		
		preg_match_all('~ZEND_BEGIN_ARG_INFO(?:_EX)?\\(([^,]*),\\s*([^,)]+)(.*?)ZEND_END_ARG_INFO~s', $file, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			$function_name = trim($val[1]);
			$local_refs[$function_name] = array();
			preg_match_all('~ZEND_ARG(?:_PASS)?_INFO\\(\\s*([^,)]+)~', $val[3], $matches2);
			$i = -1;
			foreach ($matches2[1] as $i => $val2) {
				if ($val2 && $val2 != "ZEND_SEND_BY_VAL") {
					$local_refs[$function_name][] = $i+1;
				}
			}
			if ($val[2] && $val2 != "ZEND_SEND_BY_VAL") {
				$local_refs[$function_name] = ($local_refs[$function_name] ? min($local_refs[$function_name]) : $i+2);
			}
		}
		
		if (substr($filename, -2) != ".h") {
			$files[$filename] = $file;
			
			// named functions
			preg_match_all('~(?:PHP|ZEND)_NAMED_FE\\((\\w*)\\s*,\\s*(\\w*)~', $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $val) {
				$aliases[$val[2]] = $val[1];
			}
		}
	}
	
	foreach ($files as $filename => $file) {
		if ($macros) {
			$file = preg_replace_callback('~\\b(' . implode('|', array_keys($macros)) . ')\\b(\\(.*\\))?~', 'expand_macros', $file);
		}
		
		// references
		preg_match_all("~^[ \t]*(?:ZEND|PHP)_FE\\((\\w+)\\s*,\\s*(\\w+)\\s*[,)]~m", $file, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		preg_match_all("~^[ \t]*(?:ZEND|PHP)_FALIAS\\((\\w+)\\s*,[^,]+,\\s*(\\w+)\\s*[,)]~m", $file, $matches2, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		foreach (array_merge($matches, $matches2) as $val) {
			if ($val[2][0] != "NULL") {
				$lineno = substr_count(substr($file, 0, $val[0][1]), "\n") + 1;
				if (isset($local_refs[$val[2][0]])) {
					$source_refs[strtolower($val[1][0])] = array($local_refs[$val[2][0]], $filename, $lineno);
				} elseif (isset($number_refs[$val[2][0]])) {
					$source_refs[strtolower($val[1][0])] = array($number_refs[$val[2][0]], $filename, $lineno);
				} else {
					$source_refs[strtolower($val[1][0])] = array(null, $filename, $lineno);
					echo "! " . $val[2][0] . " from $filename is not defined.\n";
				}
			}
		}
		
		// read parameters
		preg_match_all('~^(?:static )?(?:ZEND|PHP)(_NAMED)?_(?:FUNCTION|METHOD)\\(([^)]+)\\)(.*)^\\}~msU', $file, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE); // }}} is not in all sources so ^} is used instead
		foreach ($matches as $val) {
			$function_name = strtolower(trim(preg_replace('~\\s*,\\s*~', '::', ($val[1][0] ? $aliases[$val[2][0]] : $val[2][0]))));
			$function_body = $val[3][0];
			$lineno = substr_count(substr($file, 0, $val[3][1]), "\n") + 1;
			
			// return type
			if (preg_match('~(.+)::__construct$~', $function_name, $match)) {
				$return_types[$function_name] = array($match[1], $filename, $lineno);
			} elseif (!in_array($function_name, $difficult_retvals)) {
				preg_match_all("~\\b(?:RETURN|RETVAL|(?:return_value->type|Z_TYPE_P\\(return_value\\))\\s*=\\s*IS)_($retval_types)|(?:ZVAL_|convert_to_)((?i)$retval_types)(?:_ex)?\\(return_value~", $function_body, $types, PREG_SET_ORDER);
				if (preg_match_all('~()(array|object)(?:_and_properties)?_init\\(return_value~', $function_body, $matches, PREG_SET_ORDER)) {
					$types = array_merge($types, $matches);
				}
				if (preg_match('~(?:ZEND_REGISTER_RESOURCE\\(|php_stream_to_zval.*)return_value~', $function_body)) {
					$types[] = array("", "RESOURCE", "");
				}
				if ($types) {
					$type = $retval_mapping[$types[0][1] . strtoupper($types[0][2])];
					for ($i=1; $i < count($types); $i++) {
						$type1 = $retval_mapping[$types[$i][1] . strtoupper($types[$i][2])];
						if ($type1 != $type) {
							if (($type1 == "int" || $type1 == "float") && ($type == "int" || $type == "float" || $type == "number")) {
								$type = "number";
							} else {
								$type = "mixed";
								break;
							}
						}
					}
					$return_types[$function_name] = array($type, $filename, $lineno);
				} elseif (!$largedir && !preg_match('~INTERNAL_FUNCTION_PARAM_PASSTHRU|return_value~', $function_body)) {
					$return_types[$function_name] = array("void", $filename, $lineno);
				}
			}
			
			// other function call
			if (preg_match('~(\\w+)\\(INTERNAL_FUNCTION_PARAM_PASSTHRU~', $function_body, $matches2)
			&& !preg_match('~ZEND_NUM_ARGS\\(\\)~', $function_body) && $matches2[1] != "php_exec_ex"
			&& preg_match('~' . preg_quote($matches2[1], '~') . '\\(INTERNAL_FUNCTION_PARAMETERS(.*)^\\}~msU', $file, $matches2, PREG_OFFSET_CAPTURE)
			&& !preg_match('~^.*\\b(?:expected_args|behavior|st)\\b~', $matches2[1][0])
			) {
				$function_body = $matches2[1][0];
				$lineno = substr_count(substr($file, 0, $matches2[1][1]), "\n") + 1;
			}
			
			// types and optional
			if (!in_array($function_name, $difficult_params)
			&& strpos($function_body, 'zend_parse_parameters_ex') === false // indicate difficulty
			&& preg_match('~.*zend_parse(_method)?_parameters\\([^,]*,\\s*"([^"]*)"~s', $function_body, $matches2) // .* to catch last occurence
			) {
				$source_types[$function_name] = array(($matches2[1] ? substr($matches2[2], 1) : $matches2[2]), $filename, $lineno);
			} elseif (!in_array($function_name, $difficult_arg_count)) {
			
				// arguments count
				$zend_num_args = "ZEND_NUM_ARGS()";
				if (preg_match('~([a-zA-Z0-9_.]+)\\s*=\\s*ZEND_NUM_ARGS()~', $function_body, $matches2)) { // int argc = ZEND_NUM_ARGS();
					$zend_num_args = $matches2[1];
				}
				$zend_num_args = preg_quote($zend_num_args, "~");
				if (preg_match("~^([ \t]+)switch\\s*\\(\\s*$zend_num_args\\s*\\)(.*)^\\1\\}~msU", $function_body, $matches2) && preg_match('~\\bdefault\\s*:.*WRONG_PARAM_COUNT~s', $matches2[2])) {
					$source_arg_counts[$function_name] = array(array_fill(0, $max_args+1, true), $filename, $lineno);
					$source_arg_count =& $source_arg_counts[$function_name][0];
					$switch = $matches2[2];
					preg_match_all('~\\bcase\\s+([0-9]+)\\s*:~', $switch, $matches2);
					foreach ($matches2[1] as $val) {
						unset($source_arg_count[$val]);
					}
				} elseif (preg_match_all("~(?:([0-9]+)\\s*($operators)\\s*$zend_num_args|$zend_num_args\\s*($operators)\\s*([0-9]+))(?=[^}]+WRONG_PARAM_COUNT)~", $function_body, $matches2, PREG_SET_ORDER)) { //! should differentiate between || and &&
					$source_arg_counts[$function_name] = array(array(), $filename, $lineno);
					$source_arg_count =& $source_arg_counts[$function_name][0];
					foreach ($matches2 as $val) {
						$number = $val[1] . $val[4];
						$operator = strtr($val[2], "><", "<>") . $val[3]; // unify to $zend_num_args $operator $number
						switch ($operator[0]) {
						case "=":
						case "!":
							if (!$source_arg_count) {
								$source_arg_count = array_fill(0, $max_args+1, true);
							}
							unset($source_arg_count[$number]);
							break;
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
	
	foreach ($files as $filename => $file) {
		// methods
		preg_match_all('~INIT(?:_OVERLOADED)?_CLASS_ENTRY\\(.*"([^"]+)"\\s*,\\s*([^)]+)~', $file, $matches, PREG_SET_ORDER);
		foreach ($matches as $val) {
			if (preg_match('~' . preg_quote($val[2], '~') . '\\[\\](.*)\\}~sU', $file, $matches2)) {
				preg_match_all('~PHP_FALIAS\\((\\w+)\\s*,\\s*(\\w+)~', $matches2[1], $matches2, PREG_SET_ORDER);
				foreach ($matches2 as $val2) {
					$function_name = strtolower($val2[2]);
					$method_name = strtolower("$val[1]::$val2[1]");
					foreach (array("source_types", "source_arg_counts", "return_types") as $var) {
						if (isset($GLOBALS[$var][$function_name])) {
							$GLOBALS[$var][$method_name] = $GLOBALS[$var][$function_name];
						}
					}
				}
			}
		}
	}
}
echo "Sources were read.\n";

// compare with documentation
$counts = array("refs" => 0, "types" => 0, "arg_counts" => 0, "return" => 0);
$reference_path = "$phpdoc_dir/reference/" . (isset($extension) ? basename($extension) : "*");
foreach (array_merge(glob("$reference_path/*/*.xml"), glob("$reference_path/*/*/*.xml")) as $filename) {
	if (preg_match('~^(.*(?:(\\w+)</classname></ooclass>\\s*)?<methodsynopsis>(.*))<methodname>([^<]+)<(.*)</methodsynopsis>~sU', file_get_contents($filename), $matches)) {
		$lineno = substr_count($matches[1], "\n") + 1;
		$return_type = $matches[3];
		$function_name = strtolower(($matches[2] ? "$matches[2]::" : "") . trim(preg_replace('~-(>|&gt;)~', '::', $matches[4])));
		$methodsynopsis = $matches[5];
		
		// return type
		if (isset($return_types[$function_name])) {
			$counts["return"]++;
			$modifier = (preg_match('~::__construct$~', $function_name) ? "i" : "");
			if (!preg_match("~<type>(" . $return_types[$function_name][0] . ")</type>~$modifier", $return_type) && ($return_types[$function_name][0] != "object" || preg_match("~<type>($valid_types|$invalid_types)</type>~", $return_type))) {
				echo "Wrong return type in $filename on line $lineno.\n";
				echo ": (" . $return_types[$function_name][0] . ") in " . $return_types[$function_name][1] . " on line " . $return_types[$function_name][2] . ".\n";
			}
		} elseif (preg_match("~<type>(callback|$invalid_types)</type>~", $return_type)) {
			echo "Wrong return type in $filename on line $lineno.\n";
		}
		
		// references
		$source_ref = (isset($source_refs[$function_name]) ? $source_refs[$function_name] : array(null));
		preg_match_all('~<parameter( role="reference")?>~', $methodsynopsis, $matches);
		$byref = array();
		foreach ($matches[1] as $key => $val) {
			if ($val) {
				$byref[] = $key + 1;
			}
		}
		if (!in_array($function_name, $wrong_refs) 
		&& (is_int($source_ref[0]) ? $byref[0] != $source_ref[0] || count($byref) != count($matches[1]) - $source_ref[0] + 1 : $byref != $source_ref[0])
		) {
			echo (isset($source_ref[0]) ? "Parameter(s) " . (is_int($source_ref[0]) ? "$source_ref[0] and rest" : implode(", ", $source_ref[0])) : "Nothing") . " should be passed by reference in $filename on line $lineno" . (isset($source_ref[1]) ? "\n: source in $source_ref[1] on line $source_ref[2]" : "") . ".\n";
		}
		if (isset($source_ref)) {
			$counts["refs"]++;
		}
		
		// parameter types and optional
		preg_match_all('~<methodparam(\\s+choice=[\'"]opt[\'"])?>\\s*<type>([^<]+)</type>\\s*<parameter(?: role="reference")?>([^<]+)~i', $methodsynopsis, $matches); // (PREG_OFFSET_CAPTURE can be used to get precise line numbers)
		foreach ($matches[2] as $i => $val) {
			if (preg_match("~^(void|$invalid_types)\$~", $val)) {
				echo "Parameter #" . ($i+1) . " has wrong type '$val' in $filename on line " . ($lineno + $i + 1) . ".\n";
			}
		}
		if (isset($source_types[$function_name])) {
			$source_type =& $source_types[$function_name];
			$counts["types"]++;
			$optional_source = false;
			$optional_doc = false;
			$i = 0;
			$error = "";
			foreach (params_source_to_doc($source_type[0]) as $param) {
				if ($param == "optional") {
					$optional_source = true;
					continue;
				} elseif (isset($matches[2][$i])) { // sufficient number of parameters in the documentation
					if ($matches[2][$i] != $param && $param != "mixed" && ($param != "object" || preg_match("~$valid_types~", $matches[2][$i]))) {
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
		} elseif (isset($source_arg_counts[$function_name])) {
			$source_arg_count =& $source_arg_counts[$function_name];
			$counts["arg_counts"]++;
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

echo "$counts[refs]/". count($source_refs) ." references checked.\n";
echo "$counts[types]/". count($source_types) ." types checked.\n";
echo "$counts[arg_counts]/". count($source_arg_counts) ." arg counts checked.\n";
echo "$counts[return]/". count($return_types) ." return types checked.\n";
