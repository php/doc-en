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
	 * $Id: build-chms.php 329603 2013-02-28 19:55:56Z kalle $
	 */


	/**
	 * Configuration
	 *
	 * Extended		- Generates the 'enhancedchm' version along the regular ones
	 * Debug		- If enabled, output directories are not pruned
	 * PhD Beta		- If enabled, then PhD is treated as an svn checkout rather than a pear package
	 */
	define('PATH_PHP', 	'E:\software\php\php.exe');
	define('PATH_WGET',	'E:\servicesfiles\path\wget.exe');
	define('PATH_CHM', 	'E:\www\hosted\php.tuxxedo.net\www\chm');
	define('PATH_LOG', 	'E:\www\hosted\php.tuxxedo.net\www\chm\logs');
	define('PATH_DOC', 	'E:\chm\doc-all');
	define('PATH_SVN', 	'E:\chm\software\SlikSVN\bin\svn.exe');
	define('PATH_HHC', 	'E:\chm\software\HTML Help Workshop\hhc.exe');
	define('PATH_PHD', 	'E:\chm\software\phd\phd.bat');

	/**
	 * Only if PHD_BETA is set to on (Tuxxedo does not have Pear installed)
	 */
	define('PATH_PEAR', 	'');

	define('EXTENDED',	true);
	define('DEBUG',		true);
	define('PHD_BETA',	false);

	/**
	 * Fallback to a set of known languages in the event of a failure to retrieve online list.
	 */
	$ACTIVE_ONLINE_LANGUAGES = Array(
						'en'    => 'English',
						'de'    => 'German',
						'es'    => 'Spanish',
						'fr'    => 'French',
						'ja'    => 'Japanese',
						'pt_BR' => 'Brazilian Portuguese',
						'ro'    => 'Romanian',
						'tr'    => 'Turkish',
						'zh'    => 'Chinese (Simplified)',
						);


	/**
	 * Helper function for execution a program (used by build-chm-history.php too)
	 *
	 * @param	string			Path to the program to execute
	 * @param	string			(optional) Parameters to pass the this call
	 * @param	string			(optional) Log output to a specific file defined here
	 * @return	void			No value is returned
	 */
	function execute_task($title, $program, $parameters, $log)
	{
		echo(date('r') . ' ' . $title . '...' . PHP_EOL);

		if(empty($program))
		{
			return;
		}

		$cmd = sprintf('"%s"%s%s', $program, (!$parameters ?: ' ' . $parameters), (!$log ? '' : ' > ' . PATH_LOG . '\\' . $log . '.log 2<&1'));

		@popen($cmd, 'r');
	}
?>