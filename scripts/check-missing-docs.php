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
	- Skip intentionally undocumented stuff
	- Make the output more useful
	- Use some reflection?
USAGE
	Pass in the following options:
	- d: required, is the index (sqlite3 db) created by running PhD
	- Example: php check-missing-docs.php -d ./doc/phd_output/index.sqlite
*/

$options = getopt('d:');
if (empty($options['d'])) {
	echo 'ERROR: Set -d foo where foo is the path to the PhD generated sqlite index.', PHP_EOL;
	exit;
}

$doc_db = trim($options['d']);
if (!file_exists($doc_db)) {
	echo "ERROR: Unable to find a file here (Path: $doc_db). Fail.", PHP_EOL;
	exit;
}

if (!extension_loaded('PDO')) {
	echo 'ERROR: This script requires the PDO extension.', PHP_EOL;
	exit;
} else {
	$drivers = PDO::getAvailableDrivers();
	if (!in_array('sqlite', $drivers)) {
		echo 'ERROR: This script requires the PDO::sqlite driver. You have PDO but with the following drivers:', PHP_EOL;
		print_r($drivers);
		exit;
	}
}

$documented = array('functions' => array(), 'methods' => array(), 'inis' => array(), 'classes' => array());
$missing    = $documented;

$pdo   = new PDO('sqlite:' . $doc_db);
$r     = $pdo->query('SELECT lower(sdesc) as sdec, lower(docbook_id) as docbook_id FROM ids');
$table = $r->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_UNIQUE);

// Functions
$functions = get_defined_functions();
foreach($functions['internal'] as $function) {
	$function = strtolower($function);
	if(isset($table[$function])) {
		$documented['functions'][] = $function;
	} else {
		$missing['functions'][] = $function;
	}
}

// Classes and Methods$class
foreach(get_declared_classes() as $class) {
	// Classes
	$class_l = strtolower($class);
	if(isset($table[$class_l])) {
		$documented['classes'][] = $class;
	} else {
		$missing['classes'][]   = $class;
	}
	// Methods
	foreach(get_class_methods($class) as $method) {
		$method_l  = strtolower($method);

		$rm = new ReflectionMethod($class, $method);
		if(strtolower($rm->getDeclaringClass()->name) === $class_l) {

			// Variations we end up with in the docs
			// Others?
			$j = strtolower($class_l . '::'    . $method_l);
			$o = strtolower($class_l . '-&gt;' . $method_l);
			$v = strtolower($class_l . '->'    . $method_l);

			if(isset($table[$j]) || isset($table[$v]) || isset($table[$o])) {
				$documented['methods'][] = $method;
			} else {
				$missing['methods'][] = $class . '::' . $method;
			}
		}
	}
}

// Ini settings
$r     = $pdo->query("SELECT lower(docbook_id) as docbook_id FROM ids WHERE docbook_id LIKE 'ini.%'");
$table = $r->fetchAll(PDO::FETCH_COLUMN);

$inis = ini_get_all();
echo 'Scanning ini settings', PHP_EOL;
foreach ($inis as $ini => $ini_value) {
	$ini_search = 'ini.' . strtolower(str_replace('_', '-', $ini));
	
	if (in_array($ini_search, $table)) {
		$documented['inis'][] = $ini;
	} else {
		$missing['inis'][] = $ini;
	}
}

echo 'Statistics and information:', PHP_EOL;
$counts_missing = array(
	'methods'  => count($missing['methods']),
	'classes'  => count($missing['classes']),
	'inis'     => count($missing['inis']),
	'functions'=> count($missing['functions']),
);
$counts_documented = array(
	'methods'  => count($documented['methods']),
	'classes'  => count($documented['classes']),
	'inis'     => count($documented['inis']),
	'functions'=> count($documented['functions']),
);

echo "Missing documentation", PHP_EOL;
print_r($missing);

echo "Counts: Missing documentation", PHP_EOL;
print_r($counts_missing);

echo "Counts: Documented documentation", PHP_EOL;
print_r($counts_documented);

