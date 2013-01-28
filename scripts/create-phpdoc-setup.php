<?php
/*  $id$

	PHP Documentation setup script
	- Checks out the PHP doc sources, and installs PhD.
	
	Usage: 
	- Help:    php create-phpdoc-setup.php -h
	- Example: php create-phpdoc-setup.php -l en -b /my/svn
	- Creates: /my/svn/doc-en/

	TODO: 
	- Determine if this is useful
	- If this is useful, make it easier to get/download/find
	- Find/Fix bugs that most likely exist
	- Make it work on Windows
	- Test in different environments
	- Increase intelligence of several checks
	- Add notification bar when doing something (e.g., checking out, running configure.php)
	- Allow installing PhD without PEAR? From SVN?
	- Clean up the getopt stuff
	- Consider using [more intuitive] long options, since PhD requires 5.3 anyway....
*/

$configs = do_getopts();

// Only -b (svn basedir) is required. -h shows this too.
if (!empty($configs['HELP']) || empty($configs['BASEDIR_SVN'])) {
	usage();
}

// -t outputs configuration
if (!empty($configs['TEST'])) {
	print_r($configs);
	exit;
}

// Required: SVN Directory (BASEDIR_SVN)
echo_line('Checking: SVN Storage Directory existing: ', FALSE);
if (!is_dir($configs['BASEDIR_SVN'])) {
	if (!mkdir($configs['BASEDIR_SVN'], 0777, TRUE)) {
		echo_line('Fail.');
		echo_line('ERROR: The SVN storage directory ($configs[BASEDIR_SVN]) could not be created.');
		exit;
	} else {
		echo_line('No.');
		echo_line('Status:   Created SVN Storage Directory: '. $configs['BASEDIR_SVN']);
	}
} else {
	echo_line($configs['BASEDIR_SVN']);
}

// Required: php binary (PATH_PHP)
echo_line('Checking: PHP Binary:');
if (!$configs['PATH_PHP'] = get_installed_path('php', $configs['PATH_PHP'], '-v')) {
	echo_line('ERROR: Cannot find PHP Binary');
	exit;
} else {
	echo_line('Status:   PHP binary found here: ' . $configs['PATH_PHP']);
}

// Required: svn binary (PATH_SVN)
echo_line('Checking: SVN Binary:');
if (!$configs['PATH_SVN'] = get_installed_path('svn', $configs['PATH_SVN'], '--version')) {
	echo_line('ERROR: SVN is needed. I cannot find. Please install it.');
	exit;
} else {
	echo_line('Status:   Found SVN binary here: ' . $configs['PATH_SVN']);
}

// Required: a valid language selection ('all' works too) (LANG_CODE)
// Note: We use svn:externals here .... consider sparse checkouts instead
echo_line('Checking: Language selection: ', FALSE);
$command = "svn info https://svn.php.net/repository/phpdoc/modules/doc-" . $configs['LANG_CODE'];
$out     = shell_exec($command);
if (false !== stripos($out, 'Not a valid URL')) {
	echo_line('ERROR: Invalid language chosen. Chose: '. $configs['LANG_CODE']);
	exit;
} else {
	echo_line($configs['LANG_CODE']);
}

// Optional: PEAR installed (PATH_PEAR)
echo_line('Checking: PEAR installation (to install PhD with):');
if (!$configs['PATH_PEAR'] = get_installed_path('pear', $configs['PATH_PEAR'], '-V')) {
	echo_line('Warning:  Cannot find PEAR installed. Please install it.');
} else {
	echo_line('Status:   Found PEAR binary here: ' . $configs['PATH_PEAR']);
}

// Optional: PhD installation (PATH_PHD)
echo_line('Checking: PhD installation:');
if (!$configs['PATH_PHD'] = get_installed_path('phd', $configs['PATH_PHD'], '-V')) {
	echo_line('Warning:  PhD is not installed.', FALSE);
	
	if ($configs['PATH_PEAR']) {
		echo_line(' But I will attempt to install PhD later.');
	} else {
		echo_line(' But since PEAR is not installed, I cannot install it. Please consider installing PhD yourself.');
	}
} else {
	echo_line('Status:   PhD found here: ' . $configs['PATH_PHD']);
}

// Required: SVN checkout
// Checking: Is this already checked out?
// Note: If already checked out, use -u to update instead.
echo_line('Checking: Seeing if you already checked out the docs here.');
$command = 'svn co https://svn.php.net/repository/phpdoc/modules/doc-' . $configs['LANG_CODE'] . ' ' . $configs['DIR_SVN'];
if (is_phpdoc_checkout($configs)) {
	if ($configs['UPDATE_CO']) {
		echo_line('Status:   The checkout already exists, but -u was used so I am updating instead.');
		$command = 'svn up ' . $configs['DIR_SVN'];
		shell_exec($command);
	} else {
		echo_line('Warning:  This is already checked out. Pass in -u to update instead.');
		exit;
	}
} else {
	echo_line('Running:  Checking out the docs from SVN to here: ' . $configs['DIR_SVN']);
	shell_exec($command);

	echo_line('Checking: Seeing if the SVN checkout was a success: ', FALSE);
	if (is_phpdoc_checkout($configs)) {
		echo_line('Yes.');
	} else {
		// Hmm....
		echo_line('No. I am extremely confused, so will exit.');
		exit;
	}
}

chdir($configs['DIR_SVN']);
echo_line('Status:   Current working directory now: '. getcwd());

// FIXME: Capture stdout/stderr on error
echo_line('Testing:  Running doc-base/configure.php to see if the XML validates: ', FALSE);
$command = 'php doc-base/configure.php --with-lang='. $configs['LANG_CODE'];
$out = shell_exec($command . ' 2>&1');

if (false !== strpos($out, 'All good. Saving')) {
	echo_line('Yes.');
} else {
	// Hmm.... this should never happen ;)
	echo_line('No.');
}

// PhD installation
if (!$configs['PATH_PHD']) {
	echo_line('Running:  Attempting to install PhD.');
	
	if ($configs['PATH_PEAR']) {

		$command = $configs['PATH_PEAR'] . ' install doc.php.net/PhD doc.php.net/PhD_PHP';
		$out     = shell_exec($command);
	
		if (!$configs['PATH_PHD'] = get_installed_path('phd', $configs['PATH_PHD'], '-V')) {
			echo_line('Warning:  Attempted and failed to install PhD. Here was the output:');
			print_r($out);
		} else {
			echo_line('Status:   PhD is now installed.');
		}
	} else {
		// Then use PhD from SVN instead?
		echo_line('Warning:  I decided to not install PhD, without PEAR.');
	}
}

echo_line();
echo_line('INFO: Done. You now have the PHP Documentation checked out:');
echo_line('-- PHP Documentation SVN path: '. $configs['DIR_SVN']);
echo_line('-- PhD Installed:              '. (empty($configs['PATH_PHD'])  ? 'no' : $configs['PATH_PHD']));
echo_line();
echo_line('INFO: Now, some things you might want to do:');
echo_line('-- Go there     : cd ' . $configs['DIR_SVN']);
echo_line('-- Validate XML : php doc-base/configure.php');
echo_line('-- Render XHTML : phd --docbook doc-base/.manual.xml --package PHP --format xhtml');
echo_line('-- View it      : open output/php-chunked-xhtml/index.html &');
echo_line('-- Edit docs    : vim en/reference/strings/functions/strlen.xml');
echo_line('-- See diff     : svn diff -u en/reference/strings/functions/strlen.xml');
echo_line('-- Is Validate? : php doc-base/configure.php');
echo_line('-- Commit :)    : svn commit en/reference/strings/functions/strlen.xml');

/******* Function library ****************************************************/
function get_installed_path($program, $test_path = NULL, $version = '--version') {
	
	// Test $test_path, if provided by user
	if (!empty($test_path)) {
		$command = "$test_path $version 2>&1";
		$out     = shell_exec($command);
		if (false !== strpos($out, 'command not found')) {
			return $test_path;
		} else {
			// Yes, bad to echo here, but oh well
			echo_line("Warning:  The desired program ($program) was not found at path ($test_path). Will try finding it myself.");
		}
	}
	
	// Now try finding it ourselves...
	// FIXME: will this always work?
	$command = "which $program 2>&1";
	$out = shell_exec($command);
	if (false !== strpos($out, '/' . $program)) {
		return trim($out);
	}
	return false;
}
function usage() {
	$descriptions = array(
		'h' => 'Help     : This help',
		't' => 'TEST Mode: Test mode. Writes nothing. Displays your configs',
		'l' => 'Language : Country code, typically two letters. Default: en',
		'b' => 'SVN  dir : Full path to the SVN base dir, where PHP will be checked out',
		's' => 'SVN  path: Full path to the SVN binary',
		'd' => 'PhD  path: PhD renders the manual, you probably do not have it installed',
		'r' => 'PEAR path: Full path to the pear command',
		'p' => 'PHP  path: Full path to the PHP binary',
		'u' => 'SVN  Up  : Update the existing SVN checkout',
	);
	echo_line();
	echo_line('This script checks out PHP from SVN, and installs PhD.');
	echo_line('The following options are available. All but -b are optional.');
	echo_line();
	echo_line('Example: php ' . $_SERVER['SCRIPT_NAME'] . ' -b /my/svn -l en');
	echo_line('Creates: /my/svn/doc-en/ with full php documentation checkout');
	echo_line();
	foreach ($descriptions as $config_n => $config_v) {
		echo_line(' -' . $config_n . ' : ' . $config_v);
	}
	exit;
}
// Questionable
function do_getopts() {
	// Intentionally 'require' everything, as 'optional' is broken.
	$options  = getopt('b:l:p:r:d:s:uht');
	$defaults = $configs = array(
		'h' => '',     // Show usage/help information
		'b' => '',     // Base directory for the SVN Checkout
		'l' => 'en',   // Language (en, or lang code for translation)
		'p' => '',     // PHP  binary Path
		'r' => '',     // PEAR binary Path
		'd' => '',     // PHD  binary Path
		's' => '',     // SVN  binary Path
		'u' => '',     // Update the checkout, instead of checkout
		't' => '',     // Test. Outputs your configuration.
	);
	foreach ($options as $option_k => $option_v) {
		
		// This means it was set. But, I don't like false being the value.
		if (in_array($option_k, array('h','u','t')) && $option_v === false) {
			$option_v = true;
		}
			
		if (!empty($option_v)) {
			$configs[$option_k] = $option_v;
		}
	}
	$configs = array(
		'BASEDIR_SVN'	=> $configs['b'],
		'LANG_CODE'		=> $configs['l'],
		'PATH_PHP'		=> $configs['p'],
		'PATH_PEAR'		=> $configs['r'],
		'PATH_PHD'		=> $configs['d'],
		'PATH_SVN'		=> $configs['s'],
		'UPDATE_CO'		=> $configs['u'],
		'HELP'			=> $configs['h'],
		'TEST'			=> $configs['t'],
	);
	
	if (!empty($configs['BASEDIR_SVN'])) {
		$configs['DIR_SVN'] = $configs['BASEDIR_SVN'] . '/doc-' . $configs['LANG_CODE'];
	}
	return $configs;
}
function is_phpdoc_checkout($configs) {
	if (empty($configs['DIR_SVN'])) {
		echo_line('Warning:  Configuration is not set properly while testing the checkout. Massive fail!');
		exit;
	}
	// TODO: Improve this check
	if (file_exists($configs['DIR_SVN'] . '/en/reference/apc/book.xml') && file_exists($configs['DIR_SVN'] . '/doc-base/configure.php')) {
		return true;
	} else {
		return false;
	}
}	
function echo_line($line = '', $newline = TRUE) {
	echo $line;
	if ($newline) {
		echo PHP_EOL;
	}
}
