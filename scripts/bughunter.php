<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.02 of the PHP licience,     |
  | that is bundled with this package in the file LICENCE and is         |
  | avalible through the world wide web at                               |
  | http://www.php.net/license/2_02.txt.                                 |
  | If uou did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world wide web, please send a note to          |
  | license@php.net so we can mail you a copy immediately                |
  +----------------------------------------------------------------------+
  | Authors:    Maxim Maletsky <maxim@php.net>                           |
  +----------------------------------------------------------------------+
  
 $Id$
*/

/*
	REQUIRES: PHP 4.3.1-dev or higher

	Bug Hunter script is an attempt to "mechanically" scan
	and compare the documentation and C sources in order to 
	hunt down the documentation imperfections.

	This program parses the XML documentation and compares it to the C source code.
	Its goal is to hunt down inconsistencies between the current documentation and the 
	actual source code in the CVS.

	BUGHUNTER v1.0 : Hunts down the inconsistencies between the docs and the source
	code (req PHP 4.3.1-dev+)

	Usage: php bughunter.php [opts] <extension name> <files to parse>
	   Ex: php bughunter.php mcve /php4/ext/mcve/*.c

	If running from browser, create an intermediate page with this:

	$dir = 'E:/CVS/PHP';         // Top level directory of your PHP repository
	echo "<pre>";
	chdir("$dir/phpdoc/scripts/");
	include('bughunter.php');

	Access it with ?a=* to get the full set of data


	Array Structure:
	extension  >>>  function  >>>  source  >>  proto

	)

	NOTE: display_disabled_function which is located in Zend/Zend_API.c is ignored from
	this test due its illogical location

*/


/* CHANGELOG:
	02/04/03 v1.0 - Original Release
*/


/* TODO:

	* Optimize error tracking for scanning the code
	* Parse the docs structure
	* Parse the C code to get the return types where possible
	* Make a better dysplay. (Should it write its output to a file?)
	* Better document above
	* Support for void types, minding the unnecessary optional character

*/


error_reporting(E_ALL);
set_time_limit(180);

Class BugHunter {

	var $argc            = Array();
	var $argv            = Array();
	var $script_version  = '1.0';
	var $php_min_version = '4.3.1-dev';
	var $parse_all       = '';
	var $parse_ext       = '';
	var $module          = 'php4';
	var $root            = '../../';
	var $funclist        = 'phpdoc/funclist.txt';
	var $index           = Array();
	var $php_types       = Array(
		 'int'
		,'string'
		,'array'
		,'object'
		,'mixed'
		,'float'
		,'bool'
		,'resource'
	);
	var $rex_php_types   = '';

	function BugHunter() {

		// Check for PHP compatibility
		if (!version_compare($this->php_min_version, PHP_VERSION, '<=')) {
			echo "You need {$this->php_min_version} or higher!\n" .
			     "YOU HAVE: " . PHP_VERSION . "\n";
			Return Exit;
		}

		// Prepare the params
		$this->argc          = $_SERVER['argc'];
		$this->argv          = $_SERVER['argv'];
		$this->rex_php_types = implode('|', $this->php_types);


		if ($this->argc < 2 or !$this->parse_argv()) {
			$this->usage();
			Return Exit;
		}

		$skip = False;
		echo "\n\nIndexing functions from `{$this->funclist}'\n";
		foreach(explode("\r\n", $this->read_file($this->root . $this->funclist)) as $line) {
			flush();
			if(preg_match_all("/^# ([[:alnum:]_\/]+\/([[:alnum:]_]+)\/[[:alnum:]_]+\.c)$/", $line, $file, PREG_SET_ORDER)) {

				$ext                             = strtolower($file[0][2]);

				if(!file_exists($this->root . $file[0][1])) {
					$skip      = True;
					$cache = $file;
					echo "\n\tSkipping {$file[0][2]}  -  <i>{$file[0][1]} not in repository</i>...";
					Continue;
				}

				$skip = False;

				$this->{$skip? 'skipped' : 'index'}[$ext]['location']   = $file[0][1];
			}
			else {
				$this->{$skip? 'skipped' : 'index'}[$ext]['function'][] = strtolower($line);
			}
		}

		echo "\n\nParsing Extensions\n";
		if($this->parse_all) {
			foreach($this->index as $ext => $data) {
				$this->cur_ext = $ext;
				flush();
				echo "\n\t{$this->cur_ext}";
				$this->result[$ext] = $this->parse_source($this->read_file($this->root . $data['location']));
			}
		}
		else if($this->parse_ext) {
			if(!isset($this->index[$this->parse_ext])) {
				echo "\n\n";
				echo isset($this->skipped[$this->parse_ext])? "Nothing to do for {$this->parse_ext}" : "Unknown Extension {$this->parse_ext}";
				echo "\n";
				Return Exit;
			}

			$this->cur_ext = $ext;
			echo "\n\t{$this->cur_ext}";
			$this->result[$this->parse_ext] = $this->parse_source($this->read_file("{$this->root}{$this->index[$this->parse_ext]['location']}"));
		}

		Return True;
	}


	function parse_argv() {

		if($this->argv[1] == '*')
			$this->parse_all = True;
		else
			$this->parse_ext = $this->argv[1];

		Return True;
	}

	function usage() {

		echo  "\n  BUGHUNTER v{$this->script_version}"
			. "\n  Hunt down the inconsistencies between the docs and the source code (req PHP 4.3.0-dev+)"
			. "\n"
			. "\n    Usage:  {$this->argv[0]} [opts] <extension name>"
			. "\n       Ex:  {$this->argv[0]} oci8"
			. "\n"
			. "\n  Authors:  Maxim Maletsky <maxim@php.net>"
			. "\n"
		;

		Return Exit;
	}


	function read_file($filename) {
		$fp     = fopen($filename, 'rb');
		if (!$fp)
			Return '';
		$buffer = fread($fp, filesize($filename));
		fclose($fp);
		Return $buffer;
	}


	function parse_source($buffer) {

		$proto              = $function = $synopsis = $result = array();
		$rex_proto          = "/[[:space:]]*\/\*[[:space:]]*\{\{\{[[:space:]]*proto[[:space:]]*(.+)[[:space:]]*\*\//msU";
		$rex_proto_synopsis = "/^[[:space:]]*(.+)[[:space:]]+([[:alnum:]_]+)[[:space:]]*\((.*)\)[[:space:]]*((.*)[[:space:]]*)*$/";

		// Break source code into functions
		preg_match_all($rex_proto, $buffer, $proto);

		// for each proto
		for($i=0; $i<sizeof($proto[1]); $i++) {

			#echo "<i>" . $proto[1][$i] . "</i> : ";

			// Break protos into tiny pieces
			preg_match_all($rex_proto_synopsis, $proto[1][$i], $detail, PREG_SET_ORDER);

			#echo "" . $detail[0][3] . " : ";
			#echo "<i>" . $detail[0][4] . "</i>\n\n";

			if(!isset($detail[0][3])) {
				echo "\n\nFailed parsing proto: {$proto[1][$i]} in {$this->index[$this->cur_ext]['location']}\n\n";
				Return Array();
			}

			// Retrieve the original string (might not be needed)
			list($proto_str, )     = explode("\n", $proto[1][$i]);

			// Parse parameters into a structured array and debug the errors.
			list($params, $errors) = $this->parse_params(strtolower(trim($detail[0][3])));

			// Compose the resulting array
			$result[strtolower($detail[0][2])]['source']['proto']   = Array(
				 'original' => trim($proto_str)                 // Full function proto
				,'desc'     => trim($detail[0][4])              // Function description
				,'return'   => strtolower(trim($detail[0][1]))  // Return Type
				,'params'   => $params                          // Parameters
				,'errors'   => $errors                          // Parameter Errors
			);
		}

		// As in every good C function, lets return something
		Return $result;
	}



	function parse_params($param_str='') {

		$p = $params = $error = $struct = Array();

		// if no proto then there's nothing for us to do
		if(!strlen($param_str))
			Return Array($params, $error);

		$p = explode(',', $param_str);
		$optional = False;

		for($i=0; $i<sizeof($p); $i++) {

			// required or optional?
			if($optional)
				$params[$i+1]['optional'] = $optional;

			else {
				if(substr($p[$i], 0, 1) == '[')
					$params[$i+1]['optional'] = $optional = True;
				else if(substr($p[$i], -1) == '[')
					$optional = True;
			}

			$p[$i] = trim($p[$i], '[] ');

			if($p[$i] == 'void' and sizeof($p) == 1) {
				$params[$i+1]['optional']    = True;
				$params[$i+1]['type']        = 'void';
				$params[$i+1]['name']        = 'void';
			}

			// if plain text mandatory parameter
			else if(preg_match_all("/^({$this->rex_php_types}) +(\&?)([a-z]+[[:alnum:]_\|]*)$/", $p[$i], $matches, PREG_SET_ORDER)) {
				$params[$i+1]['type']        = $matches[0][1];
				$params[$i+1]['name']        = $matches[0][3];
				if(strlen($matches[0][2]))
					$params[$i+1]['referenced']  = True;
			}

			// Debug, calculate error
			else {

				// If the type passed but there is no variable name
				if(preg_match("/^({$this->rex_php_types})$/", $p[$i]))
					$error[$i+1]['no_var']  = True;

				// If the variable has no type specified
				else if(preg_match("/^(\&?)([a-z]+[[:alnum:]_\|]*)$/", $p[$i]))
					$error[$i+1]['no_type']  = True;

				// If the variable has an unknown type associated
				else if(preg_match_all("/^(.+) +(\&?)([a-z]+[[:alnum:]_\|]*)$/", $p[$i], $matches, PREG_SET_ORDER))
					$error[$i+1]['wrong_type']  = $matches[0][1];

				// If there is no variable and no type whatsoever
				else if(!strlen($p[$i]))
					$error[$i+1]['no_data']     = True;
			}
		}

		Return Array($params, $error);
	}

}


// Debugging: when runs as module
// Just add assign any key to the valuein the order

if($_SERVER['REQUEST_METHOD'] == 'GET') {
	$_SERVER['argv'][0] = basename(__FILE__);
	foreach($_GET as $v)
		$_SERVER['argv'][] = $v;
	$_SERVER['argc']   = sizeof($_SERVER['argv']);
}


// debugging
function getmicrotime(){ 
	list($usec, $sec) = explode(" ",microtime()); 
	return ((float)$usec + (float)$sec); 
}

$t_start = getmicrotime();



// and Voila!

$hunter = new BugHunter();

echo "\n\nProcess took : " . round(getmicrotime() - $t_start, 3) . " seconds\n\n\n";
echo "\n\nResults:\n\n";

$tot   = 0;
$size1 = 20;
$size2 = 30;
foreach($hunter->result as $ext => $function) {
	flush();
	echo "\n\t" . str_pad($ext, $size1);
	$err = 0;
	foreach($function as $name=>$data) {
		if(!empty($data['source']['proto']['errors'])) {

			if(!$err)
				echo "\t[  FAILED  ]";

			echo "\n\t\t" . str_pad("$name()", $size2);

			$line = '';
			foreach($data['source']['proto']['errors'] as $param=>$error) {

				echo "$line\t\t$param. ";

				if(!$line)
					$line = "\n\t\t" . str_repeat(' ', $size2);

				foreach($error as $err_type=>$err_val) {
					echo str_pad($err_type, 10) . ($err_type == 'wrong_type'? (" : " . str_pad($err_val , 10)) : '');
					$err++;
				}
			}
		}
	}
	echo ($err)? "\n\n\t\tTotal bugs: $err\n" : "\t[    OK    ]";
	$tot += $err;
}

echo "\n\nTotal proto inconsistencies: $tot";

#print_r((array) $hunter);

?>