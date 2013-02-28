<?php
	/**
	 * +----------------------------------------------------------------------+
	 * | PHP Version 5                                                        |
	 * +----------------------------------------------------------------------+
	 * | Copyright (c) 1997-2013 The PHP Group                                |
	 * +----------------------------------------------------------------------+
	 * | This source file is subject to version 3.01 of the PHP license,      |
	 * | that is bundled with this package in the file LICENSE, and is        |
	 * | available through the world-wide-web at the following url:           |
	 * | http://www.php.net/license/3_01.txt.                                 |
	 * | If you did not receive a copy of the PHP license and are unable to   |
	 * | obtain it through the world-wide-web, please send a note to          |
	 * | license@php.net so we can mail you a copy immediately.               |
	 * +----------------------------------------------------------------------+
	 * | Authors: Kalle Sommer Nielsen <kalle@php.net>                        |
	 * +----------------------------------------------------------------------+
	 * 
	 * $Id$
	 */


	/**
	 * This script rebuilds build.log used by build-chms.php ($build_history)
	 * without actually rebuilding the CHM files
	 */

	/**
	 * Configuration
	 */
	include_once __DIR__ .'/build-chms-config.php';

	/**
	 * The languages to build are retrieved from https://svn.php.net/repository/web/php/trunk/include/languages.inc
	 */
	if (file_exists(__DIR__ . '\\languages.inc'))
	{
		unlink(__DIR__ . '\\languages.inc');
	}

	execute_task('Get list of online languages', PATH_WGET, '--debug --verbose --no-check-certificate https://svn.php.net/repository/web/php/trunk/include/languages.inc --output-document=' . __DIR__ . '\\languages.inc', false);

	if (file_exists(__DIR__ . '\\languages.inc'))
	{
		include_once __DIR__ . '\\languages.inc';
	}

	/**
	 * Always build English first.
	 */
	unset($ACTIVE_ONLINE_LANGUAGES['en']);
	ksort($ACTIVE_ONLINE_LANGUAGES);

	$ACTIVE_ONLINE_LANGUAGES = array('en' => 'English') + $ACTIVE_ONLINE_LANGUAGES;

	/**
	 * Hold the results of this build
	 */
	$build_history = array();


	foreach($ACTIVE_ONLINE_LANGUAGES as $lang_code => $language)
	{
		$chm_filename 	= PATH_CHM . '\\' . 'php_manual_' . $lang_code . '.chm';
		$e_chm_filename = PATH_CHM . '\\' . 'php_enhanced_' . $lang_code . '.chm';

		if(is_file($chm_filename))
		{
			$build_history[] = array('php_manual_' . $lang_code . '.chm', md5_file($chm_filename), filemtime($chm_filename));
		}

		if(is_file($e_chm_filename))
		{
			$build_history[] = array('php_enhanced_' . $lang_code . '.chm', md5_file($e_chm_filename), filemtime($e_chm_filename));
		}
	}

	/**
	 * Save build history
	 */
	file_put_contents(PATH_CHM . '\\build.log', implode(PHP_EOL, array_map(function($single_build)
	{
		return implode("\t", $single_build);
	}, $build_history)));

	echo(date('r') . ' Done!');
?>