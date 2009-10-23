<?php
/*
Introduction:
	- This script checks for optional parameters that do not utilize the <initializer> tag.
	- Pass in a path and it'll check it. The path might include all of phpdoc, or a simple extension
TODO:
	- Have better output when using -o
	- Determine what initializer values should be as some cases aren't clear
*/

$opts = getopt('p:oh');

if (isset($opts['h'])) {
	usage();
}
if (empty($opts['p'])) {
	echo "\nERROR:\n - A path is required\n";
	usage();
}
if (!is_dir($opts['p'])) {
	echo "\nERROR:\n - Please pass in a real directory, unlike this mysterious '$opts[p]'\n";
	usage();
}

$empty = array();
$count_total = 0;
$count_empty = 0;

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($opts['p'])) as $file) {

	$filepath = $file->getPathname();
	$filename = $file->getBasename();

	if (!$file->isFile() || pathinfo($filepath, PATHINFO_EXTENSION) !== 'xml') {
		continue;
	}
	
	$contents = file_get_contents($filepath);

	$matches = array();
	preg_match_all('@<methodparam choice="opt"><type>(.*)</type><parameter>(.*)</parameter>(.*)</methodparam>@', $contents, $matches);

	// Check if any optional parameters exist
	if (empty($matches)) {
		continue;
	}

	// Log optional parameters without default values
	// We use the <initializer> DocBook tag for this task.
	foreach ($matches[3] as $match) {
		$count_total++;
		if (empty($match) || (false === strpos($match, '<initializer>'))) {
			$count_empty++;
			$empty[$filepath] = $matches;
		}
	}
}

// This output could be more useful
if (isset($opts['o'])) {
	print_r($empty);
}

print "Found $count_total optional parameters, and $count_empty are empty.\n";

function usage() {
	echo "\nUSAGE:\n";
	echo "$ php {$_SERVER['SCRIPT_FILENAME']} -p /path/to/phpdoc/docs/to/check\n";
	echo "  Optional: Add -o to output the results.\n";
	exit;
}
