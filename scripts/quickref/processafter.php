<?php
/*
# +----------------------------------------------------------------------+
# | PHP Version 4                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2003 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP licence,      |
# | that is bundled with this package in the file LICENCE and is         |
# | avalible through the world wide web at                               |
# | http://www.php.net/license/2_02.txt.                                 |
# | If you did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world wide web, please send a note to          |
# | license@php.net so we can mail you a copy immediately.               |
# +----------------------------------------------------------------------+
# | Authors:    Mitja Slenc <mitja@php.net>                              |
# +----------------------------------------------------------------------+
# 
# $Id$
*/

$lines=file("originalafter.js");
foreach($lines as $key => $line) {
	$lines[$key]=array_shift(explode("//", trim($line)));
}

$leave=array("cpd", "dcp", "for", "document", "forms", "break", "if", "continue", "var", "style", "innerHTML", "value", "getElementById", "autocomplete", "onblur", "onfocus", "onkeyup", "onkeydown", "onkeypress", "display", "pattern", "show", "left", "top", "event", "evt", "ev", "which", "length", "all", "navigator", "userAgent", "toLowerCase", "indexOf", "width", "else", "write", "split", "join", "charAt", "substring", "function", "return", "new", "Array", "switch", "case", "push", "pop", "default", "true", "false", "offsetLeft", "offsetParent", "while", "null", "tagName", "clientLeft", "parseInt", "border", "isNaN", "getAttribute", "charCode", "keyCode", "cc", "setTimeout", "fh_EBlurT");

$text=implode(" ", $lines);

while (strlen(str_replace("  ", " ", $text))<strlen($text))
	$text=str_replace("  ", " ", $text);

$pos=0;
$instring="'";
$thisone="";
$lines=array();

while ($pos<strlen($text)) {
	if ($instring) {
		if ($text[$pos]=="\\" && $text[$pos+1]==$instring) {
			$thisone.="\\".$instring;
			$pos+=2;
		} else
		if ($text[$pos]==$instring) {
			$thisone.=$instring;
			$pos++;
			$lines[]=$thisone;
			$thisone="";
			$instring=0;
		} else {
			$thisone.=$text[$pos];
			$pos++;
		}
	} else {
		if ($text[$pos]=="\"" || $text[$pos]=="'") {
			$lines[]=$thisone;
			$thisone=$text[$pos];
			$instring=$text[$pos];
			$pos++;
		} else {
			$thisone.=$text[$pos];
			$pos++;
		}
	}
}
$lines[]=$thisone;

$ids=array();

foreach($lines as $line) {
	if (!strlen($line)) continue;
	if ($line[0]=="\"" || $line[0]=="'") continue;
	preg_match_all("/[a-zA-Z_][a-zA-Z0-9_]*/", $line, $matches);
	if (sizeof($matches[0]))
		foreach($matches[0] as $id)
			$ids[$id]++;
}

foreach($leave as $toremove)
	unset($ids[$toremove]);

arsort($ids);

$rplwith=array();
for ($a=ord("a"); $a<=ord("z"); $a++)
	$rplwith[]="F".chr($a);
for ($a=ord("A"); $a<=ord("Z"); $a++)
	$rplwith[]="F".chr($a);
for ($a=ord("a"); $a<=ord("z"); $a++)
	$rplwith[]="FF".chr($a);
for ($a=ord("A"); $a<=ord("Z"); $a++)
	$rplwith[]="FF".chr($a);

$pos=0;
foreach($ids as $key => $val)
	$ids[$key]=$rplwith[$pos++];

foreach($lines as $key => $line) {
	if ($line[0]=="\"" || $line[0]=="'") continue;
	$lines[$key]=preg_replace_callback("/[a-zA-Z_][a-zA-Z0-9_]*/", "DoReplace", $line);
}
foreach($lines as $key => $line) {
	if ($line[0]=="\"" || $line[0]=="'") continue;
	$lines[$key]=preg_replace_callback("/. ./", "ReplaceSpaces", $line);
}
foreach($lines as $key => $line) {
	if ($line[0]=="\"" || $line[0]=="'") continue;
	$lines[$key]=preg_replace_callback("/. ./", "ReplaceSpaces", $line);
}

fwrite(fopen("after.js", "w"), implode("", $lines)."\n");

function ReplaceSpaces($a)
{
	$a=$a[0];
	if (ctype_alpha($a[0]) && ctype_alpha($a[2])) return $a;
	return $a[0].$a[2];
}

function DoReplace($a)
{
	global $ids;
	if (isset($ids[$a[0]]))
		return $ids[$a[0]];
	return $a[0];
}
?>
