<?php
	/**
	 * +----------------------------------------------------------------------+
	 * | PHP Version 5                                                        |
	 * +----------------------------------------------------------------------+
	 * | Copyright (c) 1997-2010 The PHP Group                                |
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
	 * TODO
	 *
	 * @todo	Check the following languages, as they generate bugged CHMS: de, es, fa, fr, pl, pt_BR, ro
	 */

	/**
	 * This script is based off the original build.chms.bat 
	 * in the root of doc-base.
	 *
	 * This script requires PHP 5.3.0+
	 */

	/**
	 * Configuration
	 */
	define('PATH_PHP', 	'C:\\php\\binaries\\PHP_5_3\\php.exe');
	define('PATH_PHD', 	'C:\\pear\\phd.bat');
	define('PATH_HHC', 	'C:\\Program Files (x86)\HTML Help Workshop\hhc.exe');
	define('PATH_SCP', 	'C:\\Program Files (x86)\PuTTY\pscp.exe');
	define('PATH_PPK', 	'C:\\php-sdk\\keys\\php-doc-host.ppk');
	define('PATH_SVN', 	'C:\\Program Files\\SlikSvn\\bin\\svn.exe');
	define('PATH_PEAR', 	'C:\\pear\\pear.bat');
	define('PATH_CHM', 	'C:\doc-all\\chmfiles');
	define('PATH_LOG', 	'C:\\doc-all\\logs');
	define('PATH_DOC', 	'C:\\doc-all');

	define('DEBUG',		true);

	/**
	 * Languages to build
	 */
	$languages = Array(
				'en', 		/* English */
				'de', 		/* German */
				'es', 		/* Spanish */
				'fa', 		/* Persian */
				'fr', 		/* French */
				'ja', 		/* Japanese */
				'pl', 		/* Polish */
				'pt_BR',	/* Brazilian Portuguese */
				'ro',		/* Romanian */
				'tr'		/* Turkish */
				);

	/**
	 * Get the current working directory
	 */
	$cwd = getcwd();

	/**
	 * Upgrade PhD, if any updates are available
	 */
	execute_task('Updating PhD', PATH_PEAR, 'upgrade doc.php.net/phd', 'pear');

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
	if($argc >= 2 && in_array($argv[1], $languages))
	{
		$languages = Array($argv[1]);
	}

	/**
	 * Start iterating over each translation
	 */
	foreach($languages as $lang)
	{
		echo('Processing language \'' . $lang . '\':' . PHP_EOL);

		/**
		 * Update that specific language folder in SVN
		 */
		chdir(PATH_DOC . '\\' . $lang . '\\');
		execute_task('- SVN', PATH_SVN, 'up', 'svn_' . $lang);
		chdir($cwd);

		/**
		 * Generate .manual.xml
		 */
		execute_task('- Configure', PATH_PHP, PATH_DOC . '\doc-base\configure.php --disable-libxml-check --with-php="' . PATH_PHP . '" --with-lang=' . $lang . ' --enable-chm', 'configure_' . $lang);

		if(!is_file(PATH_DOC . '\\doc-base\\.manual.xml'))
		{
			echo('- Build error: configure failed' . PHP_EOL);

			continue;
		}

		/**
		 * Run .manual.xml thru PhD
		 */
		execute_task('- PhD', PATH_PHD, '-d "' . PATH_DOC . '\\doc-base\\.manual.xml' . '" -P PHP -f chm -o "' . PATH_DOC . '\\tmp\\' . $lang . '" --lang=' . $lang, 'phd_' . $lang);

		if(!is_file(PATH_DOC . '\\tmp\\' . $lang . '\\php-chm\\php_manual_' . $lang . '.hhp'))
		{
			echo('- Build error: PhD failed' . PHP_EOL);

			continue;
		}

		/**
		 * Run the HTML Help Compiler to generate the actual CHM file
		 */
		execute_task('- HHC', PATH_HHC, '"' . PATH_DOC . '\\tmp\\' . $lang . '\\php-chm\\php_manual_' . $lang . '.hhp"', 'hhc_' . $lang);

		if(!is_file(PATH_DOC . '\\tmp\\' . $lang . '\\php-chm\\php_manual_' . $lang . '.chm'))
		{
			echo('- Build error: HHC failed' . PHP_EOL);

			continue;
		}

		/**
		 * Copy the CHM file into the archive
		 */
		if(!copy(PATH_DOC . '\\tmp\\' . $lang . '\\php-chm\\php_manual_' . $lang . '.chm', PATH_DOC . '\\chmfiles\\php_manual_' . $lang . '.chm'))
		{
			echo('- Build error: Unable to copy CHM file into archive folder');

			continue;
		}

		/**
		 * Update the CHM on the rsync server
		 */
		execute_task('- rsync', PATH_SCP, '-batch -q -i "' . PATH_PPK . '" -l bjori "' . PATH_DOC . '\\chmfiles\\php_manual_' . $lang . '.chm" rsync.php.net:/home/bjori/manual-chms-new/', 'rsync_' . $lang);

		/**
		 * Cleanup
		 */
		echo('- Clean up' . PHP_EOL);

		unlink(PATH_DOC . '\\doc-base\\.manual.xml');

		if(!DEBUG)
		{
			rmdir_recursive(PATH_DOC . '\\tmp\\' . $lang . '\\php-chm\\');
		}
	}

	echo('Done!');


	/**
	 * Helper function for execution a program
	 *
	 * @param	string		Path to the program to execute
	 * @param	string		(optional) Parameters to pass the this call
	 * @param	string		(optional) Log output to a specific file defined here
	 * @return	void		No value is returned
	 */
	function execute_task($title, $program, $parameters, $log)
	{
		echo($title . '...' . PHP_EOL);

		if(empty($program))
		{
			return;
		}

		@popen($p = sprintf('"%s"%s%s', $program, (!$parameters ?: ' ' . $parameters), (!$log ?: ' > ' . PATH_LOG . '\\' . $log . '.log 2<&1')), 'r');
	}

	/**
	 * Removes all files within a directory and then 
	 * deletes the directory
	 *
	 * @param	string		Directory to remove
	 * @return	void		No value is returned
	 */
	function rmdir_recursive($dir)
	{
		if(!is_dir($dir))
		{
			return;
		}

		$glob = glob($dir . '*');

		if(sizeof($glob))
		{
			foreach($glob as $obj)
			{
				if(is_dir($obj))
				{
					rmdir_recursive($obj . '\\');
				}
				else
				{
					unlink($obj);
				}
			}
		}

		rmdir($dir);
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