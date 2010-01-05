#!/usr/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2010 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Jeroen van Wolffelaar <jeroen@php.net>                   |
  +----------------------------------------------------------------------+

  $Id$
*/

// Sorts the aliases.xml file. This script assumes that </row> and <row> tags
// are not on the same line.

$lang = isset($argv[1]) ? $argv[1] : 'en';

if (@is_dir($lang)) {
	$filename = "$lang/appendices/aliases.xml";
} elseif (@is_dir("../$lang")) {
	$filename = "../$lang/appendices/aliases.xml";
} else { ?>
  Usage: ./scripts/sort_aliases.php [<lang>]

  While being in the root of phpdoc.
<?php
	exit;
}

echo "File: $filename\n\n";


$lines = file($filename);
if (!$lines) {
	echo "Cannot read from file, bailing out...\n";
	exit;
}

$out = @fopen("$filename.sorted", 'w');
if (!$out) {
	echo "Cannot write to file $filename.sorted, bailing out...\n";
	exit;
}

echo "Copying beginning of file...\n";
for ($nr = 0; !ereg('tbody',$lines[$nr]); $nr++) {
	fwrite($out, $lines[$nr]);
}
fwrite($out, $lines[$nr++]);

echo "Reading entries... ";
for (; ereg('<row>', $lines[$nr]); $nr++) {
	$entry = '';
	$key = '';
	for (; !ereg('</row>', $lines[$nr]); $nr++) {
		$entry .= $lines[$nr];
		if (!$key && ereg('<entry>([^<>]*)</entry>', $lines[$nr], $regs)) {
			$key = $regs[1];
		}
	}
	$entry .= $lines[$nr];
	if (!$key) {
		echo "No key found, key: ";
		var_dump($key);
		echo "entry: ";
		var_dump($entry);
		exit;
	}
	if (isset($entries[$key])) {
		// duplicate key, in order to get a stable sorting, add NUL byte and
		// (line number + 1000000)
		$key .= "\0".(1000000+$nr);
	}

	$entries[$key] = $entry;
}
echo count($entries)." entries found\n";

echo "Sorting entries...\n";
asort($entries);

echo "Writing sorted entries...\n";
foreach ($entries as $entry) {
	fwrite($out, $entry);
}

echo "Copying rest of file...\n";
for (;isset($lines[$nr]); $nr++) {
	fwrite($out, $lines[$nr]);
}

fclose($out);
?>
Done!
(you you still need to copy <?php echo $filename;?>.sorted
on top of <?php echo $filename;?> after a quick check)
