<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2009 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Philip Olson <philip@php.net>                            |
  |             Some code stolen from other phpdoc/scripts/              |
  +----------------------------------------------------------------------+
  
  $Id$

Introduction:

  This library is used by translation related scripts within
  the PHP Documentation repository.

Usage examples:

    // Should this file be translated?
    if (!is_translatable ($filename)) {
        exit;
    }

   // Is the translation considered current?
    if (!is_translation_current ($file_en, $file_lang)) {
        exit;
    }

*/

function is_translation_current ($filename_en, $filename_lang) {
	
	if (!is_readable ($filename_en)) {
		trigger_error("File ($filename_en) is not readable", E_USER_WARNING);
		return false;
	}
	if (!is_readable ($filename_lang)) {
		trigger_error("File ($filename_lang) is not readable", E_USER_WARNING);
		return false;
	}
	
	$en   = file_get_contents($filename_en);
	$lang = file_get_contents($filename_lang);
	
	$match_en = $match_lang = array();

	preg_match ("/<!-- .Revision: \d+\.(\d+) . -->/",    $en,   $match_en);
	preg_match ("/<!--\s*EN-Revision:\s*\d+\.(\d+)\s*/", $lang, $match_lang);
	
	if (empty($match_en[1]) || empty($match_lang[1])) {
		trigger_error("Cannot extract Revision info for (LANG: $filename_lang) (EN: $filename_lang)", E_USER_WARNING);
		return false;
	}
	
	if (trim($match_en[1]) === trim($match_lang[1])) {
		return true;
	}
	
	return false;
}

function is_translatable ($filename) {

	$files_not_translated = array(
							'rsusi.txt',
							'missing-ids.xml',
							'extensions.xml',
							'README',
							'contributors.xml',
							'contributors.ent',
							'reserved.constants.xml',
							'DO_NOT_TRANSLATE',
							'license.xml',
							'versions.xml',
	);
	
	if (in_array(basename($filename), $files_not_translated)) {
		return false;
	}
	
	$files_matches = array('/internals/', '/internals2/', 'entities.');
	
	foreach ($files_matches as $match) {
		if (false !== strpos($filename, $match)) {
			return false;
		}
	}
	
	return true;
}
