<?
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2003 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.02 of the PHP licience,     |
  | that is bundled with this package in the file LICENCE and is         |
  | avalible through the world wide web at                               |
  | http://www.php.net/license/2_02.txt.                                 |
  | If uou did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world wide web, please send a note to          |
  | license@php.net so we can mail you a copy immediately                |
  +----------------------------------------------------------------------+
  | Authors:    Moshe Doron <momo@php.net>                          	 |
  +----------------------------------------------------------------------+
  
 $Id$
*/

/*
	REQUIRES: PHP 4.3.2 or higher
	
	This file is temporary patch allow the hebrew and (in the future arabic) html generation.
	
	this script have to be run after the build proccess.
	giving the docs path it's edit the files and add dir=rtl,ltr where needed
	
	CAUTION! be careful here!
	that's 'll replace all the file with "rtl" version, so u may want first try it on partial directory.
	
	Usage: php rtlpatch path/to/html/directory
	
	script runing time on my box is about 15% then the build time, not so big deal, that'll force me rewrite the parser in C ;)

*/
error_reporting(2047);

// finding the real path of the needed files:
$mypath = $_SERVER["SCRIPT_NAME"];
$spos = strrpos($mypath,"/");
if(!$spos) $spos = strrpos($mypath,"\\");
$mypath = substr($mypath,0,$spos);
if($mypath) $mypath.="/";

$docroot = $_SERVER["argv"][1];

require "$mypath/HtmlParser.class.php";
require "$mypath/HtmlExtParser.class.php";

// clean the new files, use for debuging:
//cleanmyfiles($docroot); exit;

loopfiles($docroot);

//u may choose to comment the following line for not lose the orginal files.
replacemyfiles($docroot);


// ------------------- Functions: -----------------

function loopfiles($dirName){
	$qudir=array();
	if(!($d = @dir($dirName))){
		mysyslog("Die: $dirName doesn't exists or have no read permission!\n");
		exit;
	}
	
	while($entry = $d->read()) {
		if ($entry != "." && $entry != "..") {
			if (is_dir($dirName."/".$entry)) {
				$qudir[] = $entry;
			}else{ 
				if(eregi(".html",$entry)) {
					$qufile[] = $dirName."/".$entry;
				}
			}
		}
	}
	
	$count = count($qufile);
	for($a=0;$a<$count;$a++){
		fix_file($qufile[$a]);
	}
	
	$count = count($qudir);
	for($a=0;$a<$count;$a++){
		loopfiles($dirName."/".$qudir[$a]);
	}
	
	$d->close();
}

function fix_file($file){
mysyslog("start fixing $file");
	$txt = mygetfile($file);
	$tree = new CHtmlExtParse($txt);
//var_dump($tree);exit;
	$tree->fix_hebrew();
	$newtext = $tree->get();
	savemyfile($file,$newtext);
	$tree->unsetme();
	unset($tree);
//die("\n---".__LINE__."---".__FILE__);
}

function mygetfile($file){
	if(!($f= @fopen($file,"rb"))){
		mysyslog("$file doesn't exists or have no read permission");
		return false;
	}
	
	$ret = "";
	while($buf = fread($f,4096)){
		$ret.=$buf;
	}
	fclose($f);
	return $ret;
}

function savemyfile($file,$text){
	if(!($f= fopen("$file._fixed.html","wb"))){
		mysyslog("$file doesn't exists or have no write permission");
		return false;
	}
	fwrite($f,$text);
	fclose($f);
//die("\n---".__LINE__."---".__FILE__);
	return true;
}

function replacemyfiles($dirName){
mysyslog("replaceing the old files with new one; one way ticket...");
	if(!($d = @dir($dirName))){
		mysyslog("Die: $dirName doesn't exists or have no read permission!\n");
		exit;
	}
	
	while($entry = $d->read()) {
		if ($entry != "." && $entry != "..") {
			$dentry = $dirName."/".$entry;
			if (is_dir($dentry)) {
				replacemyfiles($dentry);
			}else{
				if(strpos($entry,"._fixed.html")) {
					$newfile = str_replace("._fixed.html","",$dentry);
					unlink($newfile);
					rename($dentry,$newfile);
				}
			}
		}
	}
	
	$d->close();
}

function mysyslog($s){
	echo "$s\n";
}
?>