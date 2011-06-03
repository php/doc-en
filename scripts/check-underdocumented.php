<?php
error_reporting(-1);
/*
Introduction:
This script checks for undocumented stuff. Currently this means anything that
uses that 'warn.undocumented.func' entity, which are underdocumented methods/functions.

Note: This script will eventually be folded into docweb and be deprecated.  However, it's
nice/simple to run it without a docweb setup so works okay for now.

Help:
$ Run this script.
*/

// p: path to phpdoc/en
// h: help
$opts = getopt('p:h::');

if (!empty($opts['h']) || empty($opts['p'])) {
	usage();
}

$phpdoc_path = $opts['p'];

if (!is_dir($phpdoc_path)) {
	echo "Hey! This ($phpdoc_path) is not a directory!", PHP_EOL;
	usage();
	exit;
}

$undocumented = array();
$count        = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($phpdoc_path)) as $file) {

	$filepath = $file->getPathname();
	$filename = $file->getBasename();
	
	if (strpos($filepath, '.svn')) {
		continue;
	}

	if (!$file->isFile() || pathinfo($filepath, PATHINFO_EXTENSION) !== 'xml') {
		continue;
	}

	$fileid = str_replace($phpdoc_path, '', $filepath);
	$ext    = strtok($fileid, DIRECTORY_SEPARATOR);
	
	if (file_exists($phpdoc_path . $fileid)) {
		$contents = file_get_contents($phpdoc_path . $fileid);
		if (false !== strpos($contents, 'warn.undocumented.func')) {
			$undocumented[$ext][] = $fileid;
			++$count;
		}
		continue;
	}
}

echo 'List of underdocumented functions/methods', PHP_EOL;
print_r($undocumented);
echo "Number underdocumented: ", $count, PHP_EOL;

function usage() {
	echo PHP_EOL, 'USAGE:', PHP_EOL;
	echo 'php ', $_SERVER['SCRIPT_FILENAME'], ' -p /path/to/phpdoc/en/reference/', PHP_EOL;
	exit;
}