<?php
	/**
	 * +----------------------------------------------------------------------+
	 * | PHP Version 5                                                        |
	 * +----------------------------------------------------------------------+
	 * | Copyright (c) 1997-2011 The PHP Group                                |
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
	 * |          Hannes Magnusson <bjori@php.net>                            |
	 * +----------------------------------------------------------------------+
	 * 
	 * $Id$
	 */


	/**
	 * This script is based off the original build.chms.bat 
	 * in the root of doc-base
	 */

	/**
	 * Configuration
	 *
	 * Extended		- Generates the 'enhancedchm' version along the regular ones
	 * Debug		- If enabled, output directories are not pruned
	 * PhD Beta		- If enabled, then PhD is treated as an svn checkout rather than a pear package
	 */
	define('PATH_PHP', 	'C:\\php\\binaries\\PHP_5_3\\php.exe');
	define('PATH_PHD', 	'C:\\pear\\phd-trunk\\phd.bat');
	define('PATH_HHC', 	'C:\\Program Files (x86)\\HTML Help Workshop\\hhc.exe');
	define('PATH_SCP', 	'C:\\Program Files (x86)\\PuTTY\\pscp.exe');
	define('PATH_PPK', 	'C:\\php-sdk\\keys\\php-doc-host.ppk');
	define('PATH_SVN', 	'C:\\Program Files\\SlikSvn\\bin\\svn.exe');
	define('PATH_PEAR', 	'C:\\pear\\pear.bat');
	define('PATH_CHM', 	'C:\\doc-all\\chmfiles');
	define('PATH_LOG', 	'C:\\doc-all\\chmfiles\\logs');
	define('PATH_DOC', 	'C:\\doc-all');
	define('PATH_WGET',	'C:\\php\\win32build\\bin\\wget.exe');

	define('EXTENDED',	true);
	define('DEBUG',		true);
	define('PHD_BETA',	true);

	/**
	 * Fallback to a set of known languages in the event of a failure to retrieve online list.
	 */
	$ACTIVE_ONLINE_LANGUAGES = Array(
				'en'    => 'English',
				'de'    => 'German',
				'es'    => 'Spanish',
				'fa'    => 'Persian',
				'fr'    => 'French',
				'ja'    => 'Japanese',
				'pl'    => 'Polish',
				'pt_BR' => 'Brazilian Portuguese',
				'ro'    => 'Romanian',
				'tr'    => 'Turkish',
				'zh'    => 'Chinese (Simplified)',
				);
	/**
	 * The languages to build are retrieved from https://svn.php.net/repository/web/php/trunk/include/languages.inc
	 */
	if (file_exists('./languages.inc'))
	{
		unlink('./languages.inc');
	}
	execute_task('Get list of online languages', PATH_WGET, '--debug --verbose --no-check-certificate https://svn.php.net/repository/web/php/trunk/include/languages.inc --output-document=' . __DIR__ . '\\languages.inc', 'wget_langs');
	if (file_exists('./languages.inc'))
	{
		include_once './languages.inc';
	}

	/**
	 * Always build English first.
	 */
	unset($ACTIVE_ONLINE_LANGUAGES['en']);
	ksort($ACTIVE_ONLINE_LANGUAGES);
	$ACTIVE_ONLINE_LANGUAGES = array('en' => 'English') + $ACTIVE_ONLINE_LANGUAGES;

	/**
	 * Get the current working directory
	 */
	$cwd = getcwd();

	/**
	 * Upgrade PhD, if any updates are available
	 */
	if(PHD_BETA)
	{
		chdir(dirname(PATH_PHD));
		execute_task('Updating PhD from svn', PATH_SVN, 'up', 'pear_svn');
		chdir($cwd);
	}
	else
	{
		execute_task('Updating PhD', PATH_PEAR, 'upgrade doc.php.net/phd', 'pear');
	}

	/**
	 * Update doc-base, to prevent build errors with 
	 * entities
	 */
	chdir(PATH_DOC . '\\doc-base\\');
	execute_task('Updating doc-base', PATH_SVN, 'up', 'docbase');
	chdir($cwd);

	/**
	 * We may want to try build a single language
	 */
	if($argc >= 2 && in_array($argv[1], array_keys($ACTIVE_ONLINE_LANGUAGES)))
	{
		$ACTIVE_ONLINE_LANGUAGES = Array($argv[1] => $ACTIVE_ONLINE_LANGUAGES[$argv[1]]);
	}

	/**
	 * Hold the results of this build
	 */
	$build_history = array();

	/**
	 * Start iterating over each translation
	 */
	foreach($ACTIVE_ONLINE_LANGUAGES as $lang_code => $language)
	{
		echo(date('r') . ' Processing language ' . $language . ' \'' . $lang_code . '\':' . PHP_EOL);

		/**
		 * Update that specific language folder in SVN
		 */
		chdir(PATH_DOC . '\\' . $lang_code . '\\');
		execute_task('- SVN', PATH_SVN, 'up', 'svn_' . $lang_code);
		chdir($cwd);

		/**
		 * Generate .manual.xml
		 */
		execute_task('- Configure', PATH_PHP, PATH_DOC . '\doc-base\configure.php --disable-libxml-check --disable-segfault-speed --with-php="' . PATH_PHP . '" --with-lang=' . $lang_code . ' --enable-chm', 'configure_' . $lang_code);

		if(!is_file(PATH_DOC . '\\doc-base\\.manual.xml'))
		{
			echo(date('r') . ' - Build error: configure failed' . PHP_EOL);

			continue;
		}

		/**
		 * Run .manual.xml thru PhD, including the enhanced build if required
		 */
		$enhanced = (EXTENDED) ? '-f enhancedchm' : '';

		execute_task('- PhD', PATH_PHD, '-d "' . PATH_DOC . '\\doc-base\\.manual.xml' . '" -P PHP -f chm ' . $enhanced . ' -o "' . PATH_DOC . '\\tmp\\' . $lang_code . '" --lang=' . $lang_code, 'phd_' . $lang_code);

		if(!is_file(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.hhp'))
		{
			echo(date('r') . ' - Build error: PhD failed' . PHP_EOL);

			continue;
		}

		/**
		 * Run the HTML Help Compiler to generate the actual CHM file
		 */
		execute_task('- HHC', PATH_HHC, '"' . PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.hhp"', 'hhc_' . $lang_code);

		if(!is_file(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.chm'))
		{
			echo(date('r') . ' - Build error: HHC failed' . PHP_EOL);

			continue;
		}
		
		/**
		 * Anything smaller than ~5MB is broken. Common broken sizes are 2MB and 15K. Common good size are 10-12MB.
		 */
		if(filesize(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.chm') < 5000000)
		{
			echo(date('r') . ' - Build error: CHM file too small, something went wrong' . PHP_EOL);
			
			continue;
		}

		/**
		 * Copy the CHM file into the archive
		 */
		if(!copy(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.chm', $s_CHMFilename = PATH_DOC . '\\chmfiles\\php_manual_' . $lang_code . '.chm'))
		{
			echo(date('r') . ' - Build error: Unable to copy CHM file into archive folder');

			continue;
		} else {
			/**
			 * Add to history
			 */
			$build_history[] = array('php_manual_' . $lang_code . '.chm', md5_file($s_CHMFilename), filemtime($s_CHMFilename));
		}

		/**
		 * Check if we are supposed to build the enhanced version
		 */
		if(EXTENDED)
		{
			/**
			 * Run the HTML Help Compiler to generate the actual CHM file
			 */
			execute_task('- [Enhanced] HHC', PATH_HHC, '"' . PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.hhp"', 'hhc_enhanced_' . $lang_code);

			if(!is_file(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.chm'))
			{
				echo(date('r') . ' - Build error: Enhanced: HHC failed' . PHP_EOL);

				goto cleanup;
			}
		
			/**
			 * Anything smaller than ~5MB is broken. Common broken sizes are 2MB and 15K. Common good size are 10-12MB.
			 */
			if(filesize(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.chm') < 5000000)
			{
				echo(date('r') . ' - Build error: Enhanced: CHM file too small, something went wrong' . PHP_EOL);
			
				goto cleanup;
			}

			/**
			 * Copy the CHM file into the archive
			 */
			if(!copy(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.chm', $s_CHMFilename = PATH_DOC . '\\chmfiles\\php_enhanced_' . $lang_code . '.chm'))
			{
				echo(date('r') . ' - Build error: Enhanced: Unable to copy CHM file into archive folder');

				goto cleanup;
			} else {
				/**
				 * Add to history
				 */
				$build_history[] = array('php_enhanced_' . $lang_code . '.chm', md5_file($s_CHMFilename), filemtime($s_CHMFilename));
			}
		}

		/**
		 * Cleanup
		 */
		cleanup:
		{
			echo(date('r') . ' - Clean up' . PHP_EOL);

			unlink(PATH_DOC . '\\doc-base\\.manual.xml');

			if(!DEBUG)
			{
				glob_recursive_apply('fsdelete', PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\');

				if(!EXTENDED)
				{
					glob_recursive_apply('fsdelete', PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\');
				}
			}
		}
	}


	/**
	 * Save build history
	 */
	file_put_contents(PATH_DOC . '\\chmfiles\\LatestCHMBuilds.txt', implode(PHP_EOL, array_map(function($single_build){ return implode("\t", $single_build);}, $build_history)));

	echo(date('r') . ' Done!');


	/**
	 * Helper function for execution a program
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

	/**
	 * Removes all files within a directory and then 
	 * deletes the directory
	 *
	 * @param	callback		Callback to the apply function
	 * @param	string			Glob directory to begin
	 * @param	string			(optional) The glob pattern to match on
	 * @return	void			No value is returned
	 */
	function glob_recursive_apply($callback, $glob_dir, $pattern = '*')
	{
		if(!is_dir($dir))
		{
			return;
		}

		$glob = glob($glob_dir . $pattern);

		if(sizeof($glob))
		{
			foreach($glob as $obj)
			{
				call_user_func($callback, $obj, $pattern);
			}
		}

		call_user_func($callback, $obj, $pattern);
	}

	/**
	 * Deletes a directory or file
	 *
	 * @param	string			Directory or filename to delete
	 * @param	string			The glob pattern to match on
	 * @return	void			No value is returned
	 */
	function fsdelete($obj, $pattern = '*')
	{
		if(is_dir($obj))
		{
			glob_recursive_apply('fsdelete', $obj . '\\', $pattern);
		}
		else
		{
			unlink($obj);
		}
	}

	/**
	 * Gets the contents of a specific log
	 *
	 * @param	string 			Name of the log (usually "program_lang")
	 * @return	string			Contents of the log
	 */
	function log_get_contents($logname)
	{
		return(@file_get_contents(PATH_LOG . '\\' . $log . '.log'));
	}
?>