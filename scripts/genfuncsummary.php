<?php

// WARNING: still a work in progress
// TODO: check why sometimes the regex is fubared - JMC
// $Id$

$php4src = realpath("../../php4");

include_once 'File/Find.php';
$find = new File_Find();
$filelist = $find->search("/.*\.(c|h|ec)$/",realpath($php4src), 'perl');
sort($filelist);

$proto_re = "/[[:space:]]*\/\*[[:space:]]*\{\{\{[[:space:]]*proto[[:space:]]*(.+)[[:space:]]*\*\//msU";
$re_split1 = "proto[[:space:]]+|\\*\/[[:space:]]*$";
$re_split2 = "\\**\/[[:space:]]*$";
$re_proto_parts = "/^(.+)[[:space:]]+([[:alnum:]_]+)\((.*)\)[[:space:]]*$/";

foreach ($filelist as $filename) {
	$proto_arr = array();
	$parse = $same = false;
	$matches = array();
	$lines = implode("\n",file($filename));
	preg_match_all($proto_re, $lines, $matches);
	if (!empty($matches[1])) {
		$name = str_replace(realpath("../../")."/", "# ", $filename);
		echo "$name\n";
		echo str_replace("\n\n","\n",implode("\n", $matches[1]))."\n";
	}
	/*
	foreach (file($filename) as $line) {
		$content = array();
		if (preg_match($proto_re, $line)) {
			list(,$proto) = split($re_split1, $line);
			$proto = trim($proto);
			$parse = $same = true;
			continue;
		} elseif (preg_match("/\*\//",$line)) {
			if ($parse) {
				$proto_info = implode(" ", $content);
				if ($same) {
					$temp = split($re_split2, $line);
					$proto_info .= " ".$temp[0];
				}
				// maybe this can be used for autogeneration of protos in the manual
				$matches = array();
				preg_match($re_proto_parts, $proto, $matches);
				$funcname = trim($matches[2]);
				$proto_arr[$funcname] = sprintf("%s\n  %s", $proto, $proto_info);
				$parse = false;
				}
			continue;
		} else {
			if ($parse && !$same) {
				$temp = split($re_split2, $line);
				$content[] = $temp[0];
			}
			$same = false;
			continue;
		}
	}
	if (!empty($proto_arr)) {
		$filename = str_replace(realpath("../../")."/", "# ", $filename);
		ksort($proto_arr);
		echo "$filename\n";
		echo implode("\n", $proto_arr)."\n";
	}
	*/
}

?>
