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
//--- Purpose: base file (all other files require this to start)

//------- Configuration 
// Root path to the phpdoc-all directory
// -- cvs co phpdoc-all
define ('CVS_ROOT_PATH', '/cvs/phpdoc-all/');

// Require User Login by email (needed for user file saving)
$requireLogin = false;

// Language information
// RTL direction is defined in $translations_RTL
$translations = array(
	'Arabic' 				=> array('ar',		'utf-8'),
	'Brazilian-PT'			=> array('pt_BR',	'iso-8859-1'),
	'Bulgarian'				=> array('bg',		'utf-8'),
	'Catalan'				=> array('ca',		'iso-8859-1'),
	'Chinese-HK'			=> array('hk',		'big5'),
	'Chinese-Simplified'	=> array('zh',		'gb2312'),
	'Chinese-Traditional'	=> array('tw',		'big5'),
	'Czech'					=> array('cs',		'iso-8859-2'),
	'Danish'				=> array('da',		'iso-8859-1'),
	'Dutch'					=> array('nl',		'iso-8859-1'),
	'Finnish'				=> array('fi',		'iso-8859-1'),
	'French'				=> array('fr',		'iso-8859-1'),
	'German'				=> array('de',		'iso-8859-1'),
	'Greek'					=> array('el',		'iso-8859-7'),
	'Hebrew'				=> array('he',		'windows-1255'),
	'Hungarian'				=> array('hu',		'iso-8859-2'),
	'Indonesian'			=> array('id',		'iso-8859-1'),
	'Italian'				=> array('it',		'iso-8859-1'),
	'Japanese'				=> array('ja',		'utf-8'),
	'Korean'				=> array('kr',		'utf-8'),
	'Lithuanian'			=> array('lt',		'iso-8859-1'),
	'Norwegian'				=> array('no',		'utf-8'),
	'Polish'				=> array('pl',		'iso-8859-2'),
	'Romanian'				=> array('ro',		'utf-8'),
	'Russian'				=> array('ru',		'utf-8'),
	'Serbian'				=> array('fa',		'utf-8'),
	'Slovak'				=> array('sk',		'iso-8859-2'),
	'Slovenian'				=> array('sl',		'iso-8859-1'),
	'Spanish'				=> array('es',		'iso-8859-1'),
	'Swedish'				=> array('sv',		'iso-8859-1'),
	'Turkish'				=> array('tr',		'utf-8'),
);

$translations_RTL = array('ar', 'he');

// Users folder where their cached files are saved - must be writable
// Only needed if $requireLogin is true
$usersCachePath = '/path/to/editor/users/';
// Files Permissions Mod
$filesChMod = 0777;	

foreach ($translations as $language => $lang_info) {
	addLanguage($language, $lang_info[0], $lang_info[1]);
} 
// Languages and paths: (ToDo Font should be defined for better display)

// Hide files (ignore)
$ignoreListingFolders = array('.', '..', 'CVS');
$ignoreListingFiles = array('contributors.xml', 'contributors.ent', 'livedocs.ent');

//------- Base functions 

function addLanguage($lang, $id, $charset='utf-8', $font='Fixedsys') {
	global $phpdocLangs, $translations_RTL;
	
	$lists = get_mailing_list_info($id);

	$phpdocLangs[$lang]['DocCVSPath'] = CVS_ROOT_PATH . $id;
	$phpdocLangs[$lang]['charset'] = $charset;
	$phpdocLangs[$lang]['direction'] = (in_array($id, $translations_RTL)) ? 'RTL' : 'LTR';
	$phpdocLangs[$lang]['id'] = $id;
	$phpdocLangs[$lang]['coordinator'] = $lists['main'];
	$phpdocLangs[$lang]['mailing'] = $lists['main'];
	$phpdocLangs[$lang]['mailingSubscribe'] = $lists['subscribe'];
	$phpdocLangs[$lang]['font'] = $font;
}

function sessionCheck($arguments='') {
	session_start();
	if (!isset($_SESSION['user'])) {
		if ($_SERVER['REQUEST_METHOD']=='GET') {			
			$_SESSION['redo'] = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
		}
		header('location: login.php'.$arguments);
		exit('<a href="login.php">Login first</a>');
	}
	return $_SESSION['user'];
}

function getDateTimeToLog() {
	return date('d/m H:i [').time().']';
}

function getUserIP() {
	return $_SERVER['REMOTE_ADDR'];
}

function getCacheName($filename) {
	// change reference/ext/file.xml  to reference~ext~file.xml	
	$filename = str_replace(array('/', '\\'), '~', $filename);
       return $filename;
}

function stripslashes2($text) {
	if (get_magic_quotes_gpc()) {
		$text = stripslashes($text);
	}
	return $text;
}

function getRevision($file, $en=false) {
	$f = fopen($file, "rb");
	$data = fread($f, 200);
	fclose($f);

	// Use en-revision
	if ($en) {
		preg_match("#<!-- *EN-Revision: +([0-9\.]+)#i", $data, $result);
		$rev = $result[1];
	} else {
		preg_match("#<!-- *\\$"."Revision: +([0-9\.]+)#i", $data, $result);
		$rev = $result[1];
	}
	return $rev;
}

function getTranslationStatus($file) {
	global $user, $phpdocLangs;

	$lang = $user['phpdocLang'];
	$translationPath = $phpdocLangs[$lang]['DocCVSPath'];	
	$trFile = $translationPath.$file;
	$enFile = CVS_ROOT_PATH . 'en/' . $file;

	$status['lastEnRevision'] = getRevision($enFile);
	if (file_exists($trFile)) {
		$status['translated'] = true;
		$status['fileRevision'] = getRevision($trFile);
		$status['fileEnRevision'] = getRevision($trFile, true);
		if ($status['fileEnRevision'] && $status['lastEnRevision']) {
			$distance = round((float) $status['lastEnRevision'], 3) - round((float) $status['fileEnRevision'], 3) ; 
			$backward = 0;
			if ($distance<0) {
				$backward = -1;
			} elseif ($distance && $distance<.03) {
				$backward = 1;
			} elseif ($distance && $distance<.2) {
				$backward = 2;
			} elseif ($distance && $distance<.4) {
				$backward = 3;
			} elseif ($distance && $distance<.6) {
				$backward = 4;
			} elseif ($distance && $distance>=.6) {
				$backward = 5;
			}
			$status['backward'] = $backward;
			$status['distance'] = $distance;			
		}
	} else {
		$status['translated'] = false;
	}	
	return $status;
}

function get_mailing_list_info ($id) {
	$list_id = strtolower(str_replace('_', '-', $id));
	return array(
		'main'		=> "doc-{$list_id}@lists.php.net",
		'subscribe' => "doc-{$list_id}-subscribe@lists.php.net",
		'archives'  => "http://news.php.net/php.doc.{$list_id}",
	);
} 
