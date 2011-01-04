<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Moshe Doron <momo@php.net>                               |
  +----------------------------------------------------------------------+
  
 $Id$
*/

/*
	REQUIRES: PHP 4.3.0 CLI or higher
	
	This file is patch allow per language customize for not pulling the autobuild system all those condition
	i making use this file to bypass some jade mysteries on the hebrew manual impossible build proccess
	
	Usage: php scriptname lang-code
*/
error_reporting(2047);
// finding the real path of the needed files:
$mypath = dirname(__FILE__)."/";

$lang= $argv[1];

if($lang=="he"){
	//replace the english dsssl entities here:
	if(file_exists("$mypath../../dsssl/docbook/common/dbl1en.ent")){
		my_shell("mv $mypath../../dsssl/docbook/common/dbl1en.ent $mypath../../dsssl/docbook/common/dbl1en.ent.org");
		my_shell("cp $mypath../../dsssl/docbook/common/dbl1he.ent $mypath../../dsssl/docbook/common/dbl1en.ent");
	}
}else{
	/*
	this section used:
	1. after the hebrew build:
	2. each start of non-hebrew build to cover the situation where the hebrew build was halted and case 1 not arrived.
	*/
	if(file_exists("$mypath../../dsssl/docbook/common/dbl1en.ent.org")){
		my_shell("mv $mypath../../dsssl/docbook/common/dbl1en.ent.org $mypath../../dsssl/docbook/common/dbl1en.ent");
	}
}

function my_shell($cmd){
	//echo "shell $cmd\n";	
	$shell = shell_exec($cmd);
	//echo $shell;
}
?>
