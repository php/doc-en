#!/usr/bin/php -q
<?php
/*
There are no restrictions on this file.
Author: Jakub Vrána <jakub@vrana.cz>

This script should stay in phpdoc-lang/scripts/ directory.
The local cvs-root is determined by its location.
*/

function exit1($status) {
	fwrite(STDERR, $status);
	exit(1);
}

if ($_SERVER["argc"] < 2 || $_SERVER["argc"] > 3) {
	exit1("Prints diff of current English file and the file used for the translation.\n"
		."Usage: ". basename(__FILE__) ." translated_file [cvs_executable]\n"
		."Example: ". basename(__FILE__) ." ../cs/appendices/about.xml /bin/cvs\n"
	);
}

// returns first 500 bytes of $filename
function head($filename) {
	$fp = fopen($filename, "rb");
	$return = fread($fp, 500);
	fclose($fp);
	return $return;
}

// find filename
$cvs_executable = (isset($_SERVER["argv"][2]) ? $_SERVER["argv"][2] : "cvs");
$root = str_replace('\\', '/', dirname(__FILE__)); // for Windows
$root = substr($root, 0, strrpos($root, '/')); // up-dir from scripts/
$filename = str_replace('\\', '/', realpath($_SERVER["argv"][1]));
if (!file_exists($filename)) {
	exit1("Error: File ". $_SERVER["argv"][1] ." not found.\n");
}
if (!ereg('^'. quotemeta($root) ."/([^/]*/.*)", $filename, $regs)) {
	exit1("Error: File ". $_SERVER["argv"][1] ." is outside CVS root.\n");
}
$filename = $regs[1];

chdir($root);
$files = (is_dir($filename) ? glob("$filename/*") : array($filename));
foreach ($files as $lang_filename) {
	if (!is_file($lang_filename)) { // do not recurse
		continue;
	}
	$en_filename = ereg_replace('^[^/]*', 'en', $lang_filename);
	
	// find EN-Revision tag
	if (!eregi("(.*)<!-- *EN-Revision: +([^ ]*)", head($lang_filename), $regs)) {
		fwrite(STDERR, "Error: Can't find EN-Revision tag in first 500 bytes of $lang_filename.\n");
	}
	$line_no = substr_count($regs[1], "\n") + 1;
	$revision = $regs[2];
	
	// compare with local version
	$same_revision = (file_exists($en_filename) && eregi('<!-- \\$Revision$en_filename), $regs) && $regs[1] == $revision);
	if (is_dir($filename)) {
		// for directories just print the list of modified files
		if (!$same_revision) {
			echo "Change in ". realpath($lang_filename) ." on line $line_no\n";
		}
	} elseif ($same_revision) {
		echo "No change in local file $en_filename.\n";
	} else {
		// execute diff
		$command = "$cvs_executable diff -u -r $revision $en_filename";
		fwrite(STDERR, "$command\n");
		passthru($command);
	}
}
?>
