#!/usr/bin/php -q
<?php
if ($_SERVER["argc"] < 3) {
	exit("Purpose: Syntax highlight PHP examples in DSSSL generated HTML manual.\n"
		.'Usage: html_syntax.php [ "html" | "php" ] [ filename.ext | dir | wildcard ] ...' ."\n"
		.'"html" - highlight_string() is used, "php" - highlight_php() is called' ."\n"
	);
}
set_time_limit(60*60); // can run long, but not more than 1 hour

function callback_html_number_entities_decode($matches) {
	return chr($matches[1]);
}

function callback_highlight_php($matches) {
	$with_tags = preg_replace_callback("!&#([0-9]+);!", "callback_html_number_entities_decode", $matches[1]);
	if ($GLOBALS["TYPE"] == "php") {
		return "\n<?php\nhighlight_php('". addcslashes($with_tags, "'\\") ."');\n?>\n";
	} else { // "html"
		return highlight_string($with_tags, true);
	}
}

$files = $_SERVER["argv"];
array_shift($files); // $argv[0] - script filename
$TYPE = array_shift($files); // "html" or "php"
while (($file = array_shift($files)) !== null) {
	if (is_file($file)) {
		$process = array($file);
	} elseif (is_dir($file)) {
		$process = glob(realpath($file) ."/*"); // realpath only for stripping slash from the end
	} else { // should be wildcard
		$process = glob($file);
	}
	foreach ($process as $val) {
        echo "$val\n";
		$original = file_get_contents($val);
		$highlighted = preg_replace_callback("!<PRE\r?\nCLASS=\"php\"\r?\n>(.*)</PRE\r?\n>!sU", "callback_highlight_php", $original);
		if ($original != $highlighted) {
            // file_put_contents is in PHP >= 5
            $fp = fopen($val, "wb");
            fwrite($fp, $highlighted);
            fclose($fp);
		}
	}
}
?>
