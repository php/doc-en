#!/usr/bin/php -q
<?php
/*
There are no restrictions on this file.
Author: Jakub Vrána <jakub@vrana.cz>

This script should stay in phpdoc-lang/scripts/ directory.
The local cvs-root is determined by it's location.
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
if (!ereg('^'. quotemeta($root) ."/([^/]*)/(.*)", $filename, $regs)) {
	exit1("Error: File ". $_SERVER["argv"][1] ." is outside CVS root.\n");
}
$lang = $regs[1];
$filename = $regs[2];

// find EN-Revision tag
chdir($root);
if (!eregi("<!-- *EN-Revision: +([^ ]*)", head("$lang/$filename"), $regs)) {
	exit1("Error: Can't find EN-Revision tag in first 500 bytes.\n");
}
$revision = $regs[1];

// execute diff
$command = "$cvs_executable diff -u -r $revision en/$filename";
fwrite(STDERR, "$command\n");
passthru($command);
?>
