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
 	//  Tools to build multibyte search enabled chm
 	define('PATH_DBCSFIX', 	'C:\Program Files (x86)\Sandcastle\ProductionTools\DBCSFix.exe');
 	define('PATH_APPLOCALE','C:\Program Files (x86)\Sandcastle\ProductionTools\SBAppLocale.exe');

	/**
	 * Only if PHD_BETA is set to on (Tuxxedo does not have Pear installed)
	 */
	define('PATH_PEAR', 	'');

	define('EXTENDED',	true);
	define('DEBUG',		true);
	define('PHD_BETA',	false);
 	define('MULTIBYTE_SEARCH',	false);

	/**
	 * Fallback to a set of known languages in the event of a failure to retrieve online list.
	 */
	$ACTIVE_ONLINE_LANGUAGES = Array(
						'en'    => 'English',
						'de'    => 'German',
						'es'    => 'Spanish',
						'fr'    => 'French',
						'it'	=> 'Italian', 
						'ja'    => 'Japanese',
						'pt_BR' => 'Brazilian Portuguese',
						'ro'    => 'Romanian',
						'tr'    => 'Turkish',
						'zh'    => 'Chinese (Simplified)',
						);


 	/**
  	 * CHM full text search does not work without compiling
  	 * with Windows Language Code Identifier(LCID).
   	 *
   	 * http://msdn.microsoft.com/en-us/library/cc233965.aspx
   	 * http://msdn.microsoft.com/ja-jp/library/cc392381.aspx
   	 */
	$CHM_FULLTEXT_SEARCH_LCID = array(
	    'en'    => 1033,
	    'ar'    => 14337,
	    'bg'    => 1026,
	    'pt_BR' => 1046,
	    'zh'    => 2052,
	    'hk'    => 3076,
	    'tw'    => 1028,
	    'ca'    => 1027,
	    'cs'    => 1029,
	    'da'    => 1030,
	    'nl'    => 1043,
	    'fi'    => 1035,
	    'fr'    => 1036,
	    'de'    => 1031,
	    'el'    => 1032,
	    'he'    => 1037,
	    'hu'    => 1038,
	    'id'    => 1057,
	    'it'    => 1040,
	    'ja'    => 1041,
	    'kr'    => 1042,
	    'lt'    => 1063,
	    'no'    => 1044,
	    'pl'    => 1045,
	    'pt'    => 2070,
	    'ro'    => 1048,
	    'ru'    => 1049,
	    'fa'    => 1065,
	    'sr'    => 3098,
	    'sk'    => 1051,
	    'sl'    => 1060,
	    'es'    => 1034,
	    'sv'    => 1053,
	    'tr'    => 1055,
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
