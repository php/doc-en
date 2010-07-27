<?php
/*  $Id$

	Description:
	This script checks the documentation for grammatical problems, and it's a port from here:
		- http://matt.might.net/articles/shell-scripts-for-passive-voice-weasel-words-duplicates/
	Read that entry for details and reasons.
	
	TODO:
	- Ideally we'd efficiently find the following facts about each problem:
		- The exact problem
		- The lines content with the problem
		- The line number
		- The filename
	- Add useful output of results
	- Add nice ability to track/save skips (e.g., skip false positives)
	- Add false positives to skip (e.g., duplicate 'variable variable')
	- Ignore repeated words that are within programlistings.
*/

// File extensions to check
$file_extensions = array('xml', 'ent', 'php');

$weasels = 'many|various|very|fairly|several|extremely|exceedingly|quite|remarkably|few|surprisingly|mostly|largely|huge|tiny|are a number|is a number|excellent|interestingly|significantly|substantially|clearly|vast|relatively|completely';

$irregulars = 'awoken|been|born|beat|become|begun|bent|beset|bet|bid|bidden|bound|bitten|bled|blown|broken|bred|brought|broadcast|built|burnt|burst|bought|cast|caught|chosen|clung|come|cost|crept|cut|dealt|dug|dived|done|drawn|dreamt|driven|drunk|eaten|fallen|fed|felt|fought|found|fit|fled|flung|flown|forbidden|forgotten|foregone|forgiven|forsaken|frozen|gotten|given|gone|ground|grown|hung|heard|hidden|hit|held|hurt|kept|knelt|knit|known|laid|led|leapt|learnt|left|lent|let|lain|lighted|lost|made|meant|met|misspelt|mistaken|mown|overcome|overdone|overtaken|overthrown|paid|pled|proven|put|quit|read|rid|ridden|rung|risen|run|sawn|said|seen|sought|sold|sent|set|sewn|shaken|shaven|shorn|shed|shone|shod|shot|shown|shrunk|shut|sung|sunk|sat|slept|slain|slid|slung|slit|smitten|sown|spoken|sped|spent|spilt|spun|spit|split|spread|sprung|stood|stolen|stuck|stung|stunk|stridden|struck|strung|striven|sworn|swept|swollen|swum|swung|taken|taught|torn|told|thought|thrived|thrown|thrust|trodden|understood|upheld|upset|woken|worn|woven|wed|wept|wound|won|withheld|withstood|wrung|written';

$opts = getopt('p:oh');

if (isset($opts['h'])) {
	usage();
}
if (empty($opts['p'])) {
	echo 'ERROR: - A path is required', PHP_EOL;
	usage();
}
if (!is_dir($opts['p'])) {
	echo 'ERROR: - Please pass in a real directory, unlike this mysterious (', $opts['p'], ')', PHP_EOL;
	usage();
}

$found       = array('duplicate' => array(), 'weasel' => array(), 'passive' => array());
$weasels_arr = explode('|', $weasels);
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($opts['p'])) as $file) {
	
	$pathname = $file->getPathname();
	
	if (!$file->isFile() || strpos($pathname, '.svn')) {
		continue;
	}

	if (!in_array(pathinfo($pathname, PATHINFO_EXTENSION), $file_extensions)) {
		continue;
	}
	$lines = file($pathname, FILE_IGNORE_NEW_LINES);
	
	/*** Duplicate words ******************************************/
	// @todo make output nicer
	if (preg_match_all('/\b(\w+)\s+\1\b/', implode($lines, "\n"), $matches)) {
		$dups = array();
		// @todo Added hack to skip 'sgml', fix this later (see todo)
		foreach ($matches as $match) {
			foreach ($match as $mat) {
				if (false !== strpos($mat, 'sgml')) {
					continue;
				}
				$dups[] = $mat;
			}
		}
		if ($dups) {
			$found['duplicate'][] = array(
				'filename'   => $pathname,
				'duplicates' => $dups,
			);
		}
	}

	/*** Passive voice ******************************************/
	// @todo get matched passive voice string
	if ($finds = preg_grep("/(am|are|were|being|is|been|was|be) ($irregulars)/i", $lines)) {
		$found['passive'][] = array(
			'filename' => $pathname,
			'lines'    => $finds,
		);
	}

	/*** Weasels ******************************************/
	/*** We want additional info (i.e., the exact weasel) for these */
	foreach ($lines as $line) {
		foreach ($weasels_arr as $weasel) {
			$position = stripos($line, " $weasel ");
			if ($position) {
				$found['weasel'][] = array(
					'position' => $position,
					'weasel'   => $weasel,
					'line'     => $line,
					'filename' => $pathname,
				);
			}
		}
	}
}

if (isset($opts['o'])) {
	print_r($found);
}

echo 'Found: ', count($found['weasel']), ' weasels, ', count($found['passive']), ' passive voice usages, and ', count($found['duplicate']), ' repeated words.', PHP_EOL;

function usage() {
	echo PHP_EOL, 'USAGE:', PHP_EOL;
	echo '$ php ', $_SERVER['SCRIPT_FILENAME'], ' -p /path/to/phpdoc/docs/dir/to/check', PHP_EOL;
	echo '  Optional: Add -o to output the results.', PHP_EOL;
	exit;
}
