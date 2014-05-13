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
	 * |          Hannes Magnusson <bjori@php.net>                            |
	 * +----------------------------------------------------------------------+
	 * 
	 * $Id$
	 */


	/**
	 * This script is based off the original build.chms.bat 
	 * in the root of doc-base
	 *
	 * This script does not work with Git, meaning enabling PHD_BETA 
	 * will fetch the old version from SVN (March, Anno 2012)
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

	execute_task('Get list of online languages', PATH_WGET, '--debug --verbose --no-check-certificate "http://git.php.net/?p=web/php.git;a=blob_plain;f=include/languages.inc;hb=HEAD" --output-document=' . __DIR__ . '\\languages.inc', 'wget_langs');

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
	 * Get the current working directory
	 */
	$cwd = getcwd();

	/**
	 * Upgrade PhD, if any updates are available
	 */
	if(PHD_BETA)
	{
		chdir(dirname(PATH_PHD));
		execute_task('Updating PhD from svn', PATH_SVN, 'up', 'phd_svn');
		chdir($cwd);
	}
	else
	{
		execute_task('Updating PhD', PATH_PEAR, 'upgrade --alldeps doc.php.net/phd', 'pear');
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
		 * Generate .manual-lang.xml
		 */
		execute_task('- Configure', PATH_PHP, PATH_DOC . '\doc-base\configure.php --disable-libxml-check --disable-segfault-speed --with-php="' . PATH_PHP . '" --with-lang=' . $lang_code . ' --enable-chm --output=' . PATH_DOC . '\\doc-base\\.manual-' . $lang_code . '.xml', 'configure_' . $lang_code);

		if(!is_file(PATH_DOC . '\\doc-base\\.manual-' . $lang_code . '.xml'))
		{
			echo(date('r') . ' - Build error: configure failed' . PHP_EOL);

			continue;
		}

		/**
		 * Run .manual.xml thru PhD, including the enhanced build if required
		 */
		$enhanced = (EXTENDED) ? '-f enhancedchm' : '';

		execute_task('- PhD', PATH_PHD, '-d "' . PATH_DOC . '\\doc-base\\.manual-' . $lang_code . '.xml' . '" -P PHP -f chm ' . $enhanced . ' -o "' . PATH_DOC . '\\tmp\\' . $lang_code . '" -L ' . $lang_code, 'phd_' . $lang_code);

		if(!is_file(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.hhp'))
		{
			echo(date('r') . ' - Build error: PhD failed' . PHP_EOL);

			continue;
		}


		/**
		 * Run DBCSFix.exe to convert Unicode to ASCII.
		 */
                $lcid = NULL;
                $multibyte_search_enabled = (MULTIBYTE_SEARCH && array_key_exists($lang_code, $CHM_FULLTEXT_SEARCH_LCID));
                if ($multibyte_search_enabled) {
                    $lcid = $CHM_FULLTEXT_SEARCH_LCID[$lang_code];
                    $dbcsfix_log = 'dbcsfix_' . $lang_code;
                    execute_task(
                        '- Convert to ASCII for Fulltext Search...',
                        PATH_DBCSFIX,
                        '/d:' . PATH_DOC . '\\tmp\\' . $lang_code
                      . ' ' . '/l:' . $lcid,
                        $dbcsfix_log);
                }

		/**
		 * Run the HTML Help Compiler to generate the actual CHM file
		 */
                if ($multibyte_search_enabled) {
                        execute_task('- HHC (with Fulltext Search)',
                            PATH_APPLOCALE,
                            $lcid . 
                            ' "' . PATH_HHC . '" "' .
                            PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.hhp"',
                           'hhc_' . $lang_code);
                } else {
			execute_task('- HHC', PATH_HHC, '"' . PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.hhp"', 'hhc_' . $lang_code);
                }

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
		if(!copy(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-chm\\php_manual_' . $lang_code . '.chm', $chm_filename = PATH_CHM . '\\' . 'php_manual_' . $lang_code . '.chm'))
		{
			echo(date('r') . ' - Build error: Unable to copy CHM file into archive folder');

			continue;
		}
		else
		{
			/**
			 * Add to history
			 */
			$build_history[] = array('php_manual_' . $lang_code . '.chm', md5_file($chm_filename), filemtime($chm_filename));
		}

		/**
		 * Check if we are supposed to build the enhanced version
		 */
		if(EXTENDED)
		{
			/**
			 * Run the HTML Help Compiler to generate the actual CHM file
			 */
	                if ($multibyte_search_enabled) {
	                        execute_task('- [Enhanced] HHC (with Fulltext Search)',
	                            PATH_APPLOCALE,
	                            $lcid .
	                            ' "' . PATH_HHC . '" "' .
	                            PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.hhp"',
	                           'hhc_enhanced_' . $lang_code);
			} else {
				execute_task('- [Enhanced] HHC', PATH_HHC, '"' . PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.hhp"', 'hhc_enhanced_' . $lang_code);
			}

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
			if(!copy(PATH_DOC . '\\tmp\\' . $lang_code . '\\php-enhancedchm\\php_manual_' . $lang_code . '.chm', $e_chm_filename = PATH_CHM . '\\' . 'php_enhanced_' . $lang_code . '.chm'))
			{
				echo(date('r') . ' - Build error: Enhanced: Unable to copy CHM file into archive folder');

				goto cleanup;
			}
			else
			{
				/**
				 * Add to history
				 */
				$build_history[] = array('php_enhanced_' . $lang_code . '.chm', md5_file($e_chm_filename), filemtime($e_chm_filename));
			}
		}

		/**
		 * Cleanup
		 */
		cleanup:
		{
			echo(date('r') . ' - Clean up' . PHP_EOL);

			unlink(PATH_DOC . '\\doc-base\\.manual-' . $lang_code . '.xml');

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
	file_put_contents(PATH_CHM . '\\build.log', implode(PHP_EOL, array_map(function($single_build)
	{
		return implode("\t", $single_build);
	}, $build_history)));

	echo(date('r') . ' Done!');


	/**
	 * Removes all files within a directory and then 
	 * deletes the directory
	 *
	 * @param	callable		Callback to the apply function
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
