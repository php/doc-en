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
//--- Purpose: allows users to login using an email address (TODO we may use CVS or other way in future)

//------- Initialization
require 'base.php';

session_start();

// Logout  &and=logout
if (isset($_GET['and'])) {
		unset($_SESSION['user']);
}


//------- Login request
if (isset($_POST['lang'])) {

	$email = '';
	if (isset($_POST['email'])) {
		$email = $_POST['email'];
	}

	$lang = $_POST['lang'];

	// Check selected language
	if (!isset($phpdocLangs[$lang])) {
		exit('Sorry, you have choosen an incorrect language');
	}

	if ($requireLogin) {
		// Simple email validation -- ToDo some enhancements
		$email = trim(strtolower($email));
		$p1 = preg_match("#^([a-z0-9][a-z0-9_\.]*)@[a-z0-9][a-z0-9\-]+\.[a-z]{2,6}$#", $email);
		$p2 = preg_match("#^([a-z0-9][a-z0-9_\.]*)@[a-z0-9][a-z0-9\-]+\.[a-z]{2,3}\.[a-z]{2}$#", $email);
	
		if (!$p1 && !$p2) {
			exit('That was not a valid email address');
		}
		// This validation is required because emails are used as user folder name -- ToDO use another way
		if (strlen($email)>50) {
			exit('That was not a valid email address');
		}

		// Do Login (create user folder)
		if (!file_exists($usersCachePath.$email)) {
			@mkdir($usersCachePath.$email, $filesChMod);
		}
		
	}
	


	if (is_dir($usersCachePath.$email) || !$requireLogin) {

		// Session setup
		$_SESSION['user'] = array('email'=>$email, 'cache'=>$usersCachePath.$email.'/', 'phpdocLang'=>$lang);

		// If expired session, redo last request
		if (!empty($_SESSION['redo'])) {
			$link = $_SESSION['redo'];
			$_SESSION['redo'] = false;
			header("location: $link");
			exit;
		}

		// If accidently lost session while editing
		if ($_REQUEST['from']=='editxml') {
			exit('Ok, Login is <b>successful</b>. Now retry your previous request');
		}

		// Redirect to frameset
		header('location: workbench.php');
		exit('<a href="workbench.php">Moved to workbench.php</a>');
	} else {
		// If folder has not been created
		exit('Sorry, there was an internal error. Please contact administration');
	}
}

//------- Login form
?>
<html>
<body style="font-family: Tahoma; font-size: 12px;">

<h3> The PHPDOC Online XML Editing Tool :: Choose your language</h3>
<form action=login.php method=post>
<input type=hidden name=from value="<?php print $_REQUEST['from']; ?>">
<?php 
if ($requireLogin) { ?>
Your email address (must be valid) <input type=text name=email><br>
<?php } ?>
Translating PHPDOC to 
<select name=lang><?php
foreach($phpdocLangs as $lang=>$langInfo) {
	print "<option value='$lang'>$lang</option>";
}
?>
</select>* <input type=submit value="Start editing">
</form>

 Developed by Salah Faya visualmind(@)php.net<br> 
 Version 1.0 - essentially developed for Arabic Translation of PHP Manual<br>
 Now updated to work with all phpdoc translations.
<br>
	This is a special tool to be used only by PHPDOC translators.<br>

Javascript and cookies must be enabled in your browser settings.<br>
<br>
<b>Last CVS Update: <?php print implode('', file('cvsupdate.txt')); ?></b>
</body>
</html>
