<?php
/*
Introduction:
This script checks for undocumented SPL goodness. It assumes the following:
- You have the latest PHP (from snaps.php.net is preferred)
- Ran phpdoc/scripts/docgen to reflect against SPL (Ex: $ php docgen.php -e spl -o spl)
- Have the latest phpdoc checkout
Help:
$ Run this script.
*/

$opts = getopt('p:d:h::');

if (!empty($opts['h']) || empty($opts['p']) || empty($opts['d'])) {
	usage();
}

$phpdoc_spl = $opts['p'];
$docgen_spl = $opts['d'];

if (!is_dir($phpdoc_spl) || !is_dir($docgen_spl)) {
	echo "Hey! These are not directories!\n";
	usage();
	exit;
}

$undocumented = array();
$missing      = array();
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docgen_spl)) as $file) {

	$filepath = $file->getPathname();
	$filename = $file->getBasename();

	$skips = array('configure.xml', 'examples.xml');
	if (!$file->isFile() || pathinfo($filepath, PATHINFO_EXTENSION) !== 'xml' || in_array($filename, $skips)) {
		continue;
	}

	$fileid = str_replace($docgen_spl, '', $filepath);
	
	if (file_exists($phpdoc_spl . $fileid)) {
		$contents = file_get_contents($phpdoc_spl . $fileid);
		if (false !== strpos($contents, 'warn.undocumented.func')) {
			$undocumented[] = $fileid;
		}
		continue;
	}
	$missing[] = $fileid;
}

echo "Missing files:\n";
print_r($missing);

echo "Exist, but considered undocumented:\n";
print_r($undocumented);

echo "Stats for SPL Docs:\n";
echo "Number missing = ", count($missing), " and undocumented: ", count($undocumented), "\n";

function usage() {
	echo "\nUSAGE:\n";
	echo "php check-missing-spldocs.php -p /path/to/phpdoc/en/reference/spl/ -d /path/to/phpdoc/scripts/docgen/spl/\n";
	exit;
}