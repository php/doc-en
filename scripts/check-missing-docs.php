<?php
/*
$Id$

Hack to check if functions/methods/inis are documented.

This _WILL_ give false positives due to the following reasons:
	- The way we document OOP/Procedural together; has id issues
	- We don't document all aliases, especially old ones
	- Various inconsistencies in the documentation (which this may help find)
	- A lot of this has to do with how we document OOP, research this (e.g., inheritance)
	- It only checks what you have compiled into your current PHP
	- Beware: Some things are intentionally not documented (e.g., leak(), php_egg_logo_guid())
	- The script isn't perfect?!?! But, it's a decent start
TODO
	- Deal with the above, hopefully fix
	- Deal with aliases (store them and/or find where/how they are stored, then use this info
	- Make the output more useful
	- Use some reflection?
USAGE
	Pass in the following options:
	- d: required, is the index (sqlite3 db) created by running PhD
	- v: optional, is not very useful, except it outputs stuff as the script runs
	- Example: php check-missing-docs.php -d ./doc/phd_output/index.sqlite -v
*/

$options = getopt('d:v');
if (empty($options['d'])) {
	echo "ERROR: Set -d to the path of your phd generated php doc index. And optionally pass in -v to output more stuff.\n";
	exit;
}

$doc_db = trim($options['d']);
if (!file_exists($doc_db)) {
	echo "ERROR: Unable to find a file here (Path: $doc_db). Fail.\n";
	exit;
}

if (!extension_loaded('sqlite3')) {
	echo "ERROR: This script requires the sqlite3 extension.\n";
	exit;
}

define ('VERBOSE', (isset($options['v']) ? TRUE : FALSE));

$undefined       = array('functions' => array(), 'methods' => array(), 'inis' => array(), 'classes' => array());
$counts_defined  = array('functions' => 0,       'methods' => 0,       'inis' => 0,       'classes' => 0);

if (!$db = new SQLite3($doc_db)) {
	echo "ERROR: Failed to utilize the powers of sqlite3.\n";
	exit;
}

// Functions
echo "Scanning functions\n";
$functions = get_defined_functions();
foreach ($functions['internal'] as $function) {
	$sql = "SELECT count(*) FROM ids WHERE LOWER(sdesc) = '" . strtolower($function) . "'";
	
	if ($db->querySingle($sql) === 0) {
		if (VERBOSE) {
			echo $function . "\n";
		}
		$undefined['functions'][] = $function;
	} else {
		++$counts_defined['functions'];
	}
}

// Classes and Methods
$classes = get_declared_classes();
echo "Scanning classes and methods\n";
foreach ($classes as $class) {

	// FIXME Not all classes have class.classname (why?)
	$sql = "SELECT count(*) FROM ids WHERE LOWER(docbook_id) = 'class." . strtolower($class) . "'";

	if ($db->querySingle($sql) === 0) {
		if (VERBOSE) {
			echo $class . "\n";
		}
		$undefined['classes'][] = $class;
	} else {
		++$counts_defined['classes'];
	}
	
	$methods = get_class_methods($class);
	
	foreach ($methods as $method) {
		$tmp = "$class::$method";
		$sql = "SELECT count(*) FROM ids WHERE LOWER(sdesc) = '" . strtolower($tmp) . "'";

		if ($db->querySingle($sql) === 0) {

			// Does this fully work? :)
			$rm = new ReflectionMethod($class, $method);
			$rp = $rm->getDeclaringClass();
			
			// Skip if inherited
			if ($rp->name !== $class) {
				continue;
			}
			
			if (VERBOSE) {
				echo $tmp . "\n";
			}
			$undefined['methods'][] = $tmp;
		} else {
			++$counts_defined['methods'];
		}
	}
}

// Ini settings
$inis = ini_get_all();
echo "Scanning ini settings\n";
foreach ($inis as $ini => $ini_value) {
	$ini_search = str_replace('_', '-', $ini);
	$sql = "SELECT count(*) FROM ids WHERE LOWER(docbook_id) = 'ini." . strtolower($ini_search) . "'";
	
	if ($db->querySingle($sql) === 0) {
		if (VERBOSE) {
			echo $ini . "\n";
		}
		$undefined['inis'][] = $ini;
	} else {
		++$counts_defined['inis'];
	}
}

echo "Statistics and information:\n";
$counts_undefined = array(
	'methods'  => count($undefined['methods']),
	'classes'  => count($undefined['classes']),
	'inis'     => count($undefined['inis']),
	'functions'=> count($undefined['functions']),
);

echo "Undefined stuff\n";
print_r($undefined);

echo "Counts: Undefined stuff\n";
print_r($counts_undefined);

echo "Counts: Defined stuff\n";
print_r($counts_defined);
