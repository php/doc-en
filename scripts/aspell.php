#!/usr/bin/php
<?php
/*
There are no restrictions on this file.
Author: Jakub Vrána <jakub@vrana.cz>
See en.pws for list of ignored words.
*/

if ($_SERVER["argc"] != 3 || ($_SERVER["argv"][1] != "escape" && $_SERVER["argv"][1] != "unescape")) {
	exit("Purpose: Escape or unescape all *.xml files for use in aspell.\n"
		. "Usage: aspell.php escape|unescape <directory>\n"
	);
}

// TODO: &xxx.xx; -> &xxx-xx;
$GOOD_TAGS = "type|parameter|function|refname|literal|methodname|abbrev|acronym|constant|varname|replaceable|filename|userinput|command|structname|structfield";
$MODE = $_SERVER["argv"][1];

// htmlentities in comments and CDATA
function callback_htmlentities($matches) {
	return $matches[1] . ($GLOBALS["MODE"] == "escape" ? htmlentities($matches[2]) : html_entity_decode($matches[2])) . $matches[3];
}

// make attributes from contents of always-good tags
function callback_make_value($matches) {
	return '<' . $matches[1] . $matches[2] . ' aspell="' . htmlentities($matches[3]) . '"/>';
}

// make contents from attributes of always-good tags
function callback_make_contents($matches) {
	return '<' . $matches[1] . $matches[2] . '>' . html_entity_decode($matches[3]) . '</' . $matches[1] . '>';
}

function recurse($dir) {
	echo "$dir\n";
	foreach (glob("$dir/*") as $filename) {
		if (is_dir($filename)) {
			recurse($filename);
		} elseif (eregi('\\.xml$', $filename)) {
			//~ echo "$filename\n";
			$file = file_get_contents($filename);
			$file = preg_replace_callback('~(<!\\[CDATA\\[)(.*)(\\]\\]>)~sU', "callback_htmlentities", $file);
			$file = preg_replace_callback('~(<!--)(.*)(-->)~sU', "callback_htmlentities", $file); // isn't in one function as is can match !CDATA[[...-->
			if ($GLOBALS["MODE"] == "escape") {
				$file = preg_replace_callback('~<(' . $GLOBALS['GOOD_TAGS'] . ')( [^>]*)?>(.*)</\\1>~sU', "callback_make_value", $file);
			} else { // "unescape"
				$file = str_replace("\r", "", $file); // for Windows version of Aspell
				$file = preg_replace_callback('~<(' . $GLOBALS['GOOD_TAGS'] . ')( [^>]*)? aspell="(.*)"/>~sU', "callback_make_contents", $file);
			}
			$fp = fopen($filename, "wb");
			fwrite($fp, $file);
			fclose($fp);
		}
	}
}

recurse($_SERVER["argv"][2]);
?>
