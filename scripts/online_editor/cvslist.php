<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors : Salah Faya <visualmind@php.net>                            |
  +----------------------------------------------------------------------+

*/
//-- The PHPDOC Online XML Editing Tool 
//--- Purpose: lists the folders and files in the left frame

//------- Initialization
require 'base.php';
$user = sessionCheck();

$lang = $user['phpdocLang'];
$translationPath = $phpdocLangs[$lang]['DocCVSPath'];

//------- Folder navigation vars
$path = '';
$file = '';
if (isset($_REQUEST['path'])) {
	$path = $_REQUEST['path'];
	if (isset($_REQUEST['file'])) {
		$file = $_REQUEST['file'];
	}
}

// Prevent wrong paths 
if (strstr($path, '.') || $file=='..' || $file=='.') {
	exit;
}


// Path Slashes
if (!$path) $path = '/';
if (substr($path, -1)!='/') $path .= '/';


// Usually, the path are based on english cvs
$target = CVS_ROOT_PATH . 'en/' . $path . $file;


//------- Decide what to do

// If file is selected, redirect to the editor..

if ($file) {
	// Find in user path first (editing cached file by default)
	$ffile = $user['cache'].getCacheName($path.$file);
	if (file_exists($ffile)) {
		header("location: editxml.php?file=$path$file&source=upath");
		exit;
	}

	// Find in translated path (edit the translated file)
	$ffile = "$translationPath$path$file";
	if (file_exists($ffile)) {
		header("location: editxml.php?file=$path$file&source=apath");
		exit;
	}

	// So it's a new file? Use the english source
	if (file_exists($target)) {
		header("location: editxml.php?file=$path$file&source=epath");
		exit;
	}
} else {

	// No file selected, Listing (from english cvs)
	$d = opendir($target);
	while($f = readdir($d)) {
		if (is_dir($target.$f)) {
			if (in_array($f, $ignoreListingFolders)) continue;
			$dirs[] = $f;
		} elseif ((substr($f, -4)=='.xml' || substr($f, -4)=='.ent') && !in_array($f, $ignoreListingFiles)) {
			$files[] = $f;
		}
	}

}


//------- Functions 
function printPathAsLinks($path) {
	global $lang;

	$px = explode('/', $path);
	$pp = '';
	foreach($px as $i=>$p) {
		$pp .= $p.'/';
		if (!$p && !$i) $p = $lang;  
		print "<a href='?path=$pp'>$p</a>";
		if ($p || !$i) print '/';
	}
	print '<br>';
}


function printDirectories($dirs, $path) {
	sort($dirs);
	foreach($dirs as $dir) {
		print "<a href='?path=$path$dir'><img src='images/dir.png' width=16 height=16 border=0 align=absmiddle> $dir</a> <br>";
	}
}

function printFiles($files, $path) {
	global $translationPath, $user;
	
	sort($files);
	foreach($files as $fl) {
		$status = getTranslationStatus($path.$fl);
		if ($status['translated']) {
			if ($status['fileEnRevision'] && $status['lastEnRevision'] && $status['distance']>=0) {
				if (!$status['backward']) {
					$title = 'Up to date';
					$img = 'images/up2date.png';
				} else {
					$title = 'Outdated '.$status['fileEnRevision'].' / '.$status['lastEnRevision'];
					$img = 'images/r'.($status['backward']+1).'.png';
				}
			} else {
				$title = 'EN-Revision is incorrect or missing';
				$img = 'images/unknown.png';
			}
		} else {
			$title = 'Not translated yet';
			$img = 'images/notyet.png';
		}

		print "<a href='?path=$path&file=$fl' target=fileframe><img src='images/text.png' width=16 height=16 border=0 align=absmiddle> $fl</a> <img src='$img' border=0 align=absmiddle alt='$title'><br>";
	}

	// Lists the user's cached files
	if (file_exists($user['cache']."files.txt")) {
		print '<hr>';
		print 'Cached files:<br>';
		$ff = file($user['cache']."files.txt");
		foreach($ff as $f) {
			$fx = explode('|', $f);
			if (!isset($cf[$fx[2]])) {
				print "<a href='editxml.php?file=$fx[2]&source=upath' target=fileframe>$fx[2]</a><br>";
				$cf[$fx[2]] = true;
			}
		}
	}
	
}


//------- Output
?>
<html>
<body bgcolor="#FFFFFF">
<style type="text/css">
body {
	font-family: Tahoma;
	font-size: 12px;
}
a:link    { 
	text-decoration: none;
	color: #000066;
}

a:hover   { 
	text-decoration: none;
	color: #ff0000;
}

a:active  { 
	text-decoration: none;
	color: #ff0000;
}

a:visited { 
	text-decoration: none;
	color: #000066;
}

	</style>
<div style="width: 400px;">
Listing:
<?php

printPathAsLinks($path);

printDirectories($dirs, $path);

printFiles($files, $path);

?>
<hr>

<form action=login.php method=post target=_top>
<?php 
if ($requireLogin) { ?>
	<input type=hidden name=email value="<?php print $user['email']; ?>"><br>
<?php } ?>
Switch language:<br> 
<select name=lang onchange="if (this.value) this.form.submit();">
<option value=''></option>
<?php
foreach($phpdocLangs as $lng=>$langInfo) {
	if ($lng==$lang) $sel = " selected"; else $sel = '';
	print "<option value='$lng'$sel>$lng</option>";
}
?>
</select>
</form>

<hr>
Legend:<br>
<img src=images/notyet.png> Not yet translated<br>
<img src=images/up2date.png> File is translated and up to date<br>
<img src=images/unknown.png> EN-Revision is incorrect or missing<br>
<img src=images/r6.png> How much is outdated-<br> gauge (hover it for revision).

</div>
</body>
</html>
