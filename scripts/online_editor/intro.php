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
  | Authors : Salah Faya <visualmind@php.net>                            |
  +----------------------------------------------------------------------+

  $Id$
*/
//-- The PHPDOC Online XML Editing Tool 
//--- Purpose: just an introduction
// ToDo show summary of translated files (or revcheck)

require 'base.php';
$user = sessionCheck();
$lang = $user['phpdocLang'];

?>
<html>
<body style="font-family: Tahoma; font-size: 12px;">

<h2> The PHPDOC Online XML Editing Tool </h2>
 Developed by Salah Faya visualmind(@)php.net<br> 
 Version 1.0 - essentially developed for Arabic Translation of PHP Manual<br>
 Now updated to work with all phpdoc translations<br>
<br>
<b>Last CVS Update: <?php print implode('', file('cvsupdate.txt')); ?></b>
<br><br>
The language you are editing is <b><?php print $lang; ?></b><br>
Translation coordinator: <b><?php print $phpdocLangs[$lang]['coordinator'] ?></b><br>
Mailing list: <b><?php print $phpdocLangs[$lang]['mailing'] ?></b>. For Subscription email to <b><?php print $phpdocLangs[$lang]['mailingSubscribe'] ?></b><br><br>
Charset used: <b><?php print $phpdocLangs[$lang]['charset'] ?></b><br>


<?php

	// Lists the user's cached files
	if (file_exists($user['cache']."files.txt")) {
		print '<hr>';
		print 'Your Cached files:<br>';
		$ff = file($user['cache']."files.txt");
		foreach($ff as $f) {
			$fx = explode('|', $f);
			if (!isset($cf[$fx[2]])) {
				print "<a href='editxml.php?file=$fx[2]&source=upath' target=fileframe>$fx[2]</a><br>";
				$cf[$fx[2]] = true;
			}
		}
	}

?>

</body>
</html>
