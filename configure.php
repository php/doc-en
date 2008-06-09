<?php // vim: ts=4 sw=4 et tw=78 fdm=marker

/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Dave Barr <dave@php.net>                                 |
  |             Hannes Magnusson <bjori@php.net>                         |
  |             Gwynne Raskind <gwynne@php.net>                          |
  +----------------------------------------------------------------------+
  
  $Id$
*/

error_reporting(E_ALL);
$cvs_id = '$Id$';

echo "configure.php: $cvs_id\n";

function usage() // {{{
{
    global $acd;
    
    echo <<<HELPCHUNK
configure.php configures this package to adapt to many kinds of systems, and PhD
builds too.

Usage: ./configure [OPTION]...

Defaults for the options are specified in brackets.

Configuration:
  -h, --help                Display this help and exit
  -V, --version             Display version information and exit
  -q, --quiet, --silent     Do not print `checking...' messages
      --srcdir=DIR          Find the sources in DIR [configure dir or `.']

Package-specific:
  --enable-force-dom-save   Force .manual.xml to be saved in a full build even
                            if it fails validation [{$acd['FORCE_DOM_SAVE']}]
  --enable-chm              Enable Windows HTML Help Edition pages [{$acd['CHMENABLED']}]
  --enable-internals        Include internals documentation [{$acd['INTERNALSENABLED']}]
  --enable-xml-details      Enable detailed XML error messages [{$acd['DETAILED_ERRORMSG']}]
  --with-php=PATH           Path to php CLI executable [detect]
  --with-inipath=PATH       Path to php.ini file [@srcdir@/scripts]
  --with-lang=LANG          Language to build [{$acd['LANG']}]
  --with-partial=ID         Root ID to build [{$acd['PARTIAL']}]

HELPCHUNK;
} // }}}

function errbox($msg) {
    $len = strlen($msg)+4;
    $line = "+" . str_repeat("-", $len) . "+";

    echo $line, "\n";
    echo "|  ", $msg, "  |", "\n";
    echo $line, "\n\n";
}
function errors_are_bad($status) {
    echo "\nEyh man. No worries. Happ shittens. Try again after fixing the errors above.\n";
    exit($status);
}

function is_windows() {
    return strncmp(strtoupper(PHP_OS), "WIN", 3) === 0;
}

function checking($for) // {{{
{
    global $ac;
    
    if ($ac['quiet'] != 'yes') {
        echo "Checking {$for}... ";
        flush( STDOUT );
    }
} // }}}

function checkerror($msg) // {{{
{
    global $ac;
    
    if ($ac['quiet'] != 'yes') {
        echo "\n";
    }
    echo "error: {$msg}\n";
    exit(1);
} // }}}

function checkvalue($v) // {{{
{
    global $ac;
    
    if ($ac['quiet'] != 'yes') {
        echo "{$v}\n";
    }
} // }}}

function abspath($path) // {{{
{
    // realpath() doesn't return empty for empty on Windows
    if ($path == '') {
        return '';
    }
    return str_replace('\\', '/', function_exists('realpath') ? realpath($path) : $path);
} // }}}

function quietechorun($e) // {{{
{
    // enclose in "" on Windows for PHP < 5.3
    if (is_windows() && phpversion() < '5.3') {
        $e = '"'.$e.'"';
    }

    passthru($e);
} // }}}

function find_file($file_array) // {{{
{
    $paths = explode(PATH_SEPARATOR, getenv('PATH'));

    if (is_array($paths)) {
        foreach ($paths as $path) {
            foreach ($file_array as $name) {
                if (file_exists("{$path}/{$name}") && is_file("{$path}/{$name}")) {
                    return "{$path}/{$name}";
                }
            }
        }
    }

    return '';
} // }}}

// Recursive glob() with a callback function {{{
function globbetyglob($globber, $userfunc)
{
    foreach (glob("$globber/*") as $file) {
        if (is_dir($file)) {
            globbetyglob($file, $userfunc);
        } else {
            call_user_func($userfunc, $file);
        }
    }
} // }}}

function find_dot_in($filename) // {{{
{
    if (substr($filename, -3) == '.in') {
        $GLOBALS['infiles'][] = $filename;
    }
} // }}}

function generate_output_file($in, $out, $ac) // {{{
{
    $data = file_get_contents($in);

    if ($data === false) {
        return false;
    }
    foreach ($ac as $k => $v) {
        $data = preg_replace('/@' . preg_quote($k) . '@/', $v, $data);
    }

    return file_put_contents($out, $data);
} // }}}

function make_scripts_executable($filename) // {{{
{
    if (substr($filename, -3) == '.sh') {
        chmod($filename, 0755);
    }
} // }}}

// Loop through and print out all XML validation errors {{{
function print_xml_errors($details = true) {
    $errors = libxml_get_errors();
    if ($errors && count($errors) > 0) {
        foreach($errors as $err) {
            // Skip all XInclude errors
            if (!strpos($err->message, 'xi:include') && !strpos($err->message, 'element include')) {
                $errmsg = wordwrap(" " . trim($err->message), 80, "\n ");
                if ($details) {
                    $file = file(urldecode($err->file)); // libxml appears to urlencode() its errors strings
                    if (isset($file[$err->line])) {
                        $line = rtrim($file[$err->line - 1]);
                        $padding = str_repeat("-", $err->column) . "^";
                        fprintf(STDERR, "\nERROR (%s:%s:%s)\n%s\n%s\n%s\n", $err->file, $err->line, $err->column, $line, $padding, $errmsg);
                    } else {
                        fprintf(STDERR, "\nERROR (%s:unknown)\n%s\n", $err->file, $errmsg);
                    }
                } else {
                    fprintf(STDERR, "%s\n", $errmsg);
                }
                if (strpos($errmsg, "chunk is not well balanced") !== false || strpos($errmsg, "Failure to process entity") !== false) {
                    fprintf(STDERR, "\n\nPrevious errors too severe. Stopping here.\n\n");
                    // Unbalanced chunk, no point in displaying more errors
                    break;
                }
            }
        }
    }
    libxml_clear_errors();
} // }}}

$srcdir = dirname(__FILE__);

// Settings {{{
$cygwin_php_bat = "{$srcdir}/../phpdoc-tools/php.bat";
$php_bin_names = array('php', 'php5', 'cli/php', 'php.exe', 'php5.exe', 'php-cli.exe', 'php-cgi.exe');
$nsgmls_bin_names = array('nsgmls', 'onsgmls', 'nsgmls.exe', 'onsgmls.exe');
// }}}

// Reject old PHP installations {{{
if (phpversion() < 5) {
    echo "PHP 5 or above is required. Version detected: " . phpversion() . "\n";
    exit(100);
} else {
    echo "PHP version: " . phpversion() . "\n";
} // }}}

echo "\n";

$acd = array( // {{{
    'srcdir' => $srcdir,
    'quiet' => 'no',
    'WORKDIR' => $srcdir,
    'SRCDIR' => $srcdir,
    'PHP' => '',
    'INIPATH' => "{$srcdir}/scripts",
    'CHMENABLED' => 'no',
    'CHMONLY_INCL_BEGIN' => '<!--',
    'CHMONLY_INCL_END' => '-->',
    'INTERNALSENABLED' => 'yes',
    'INTERNALS_EXCL_BEGIN' => '',
    'INTERNALS_EXCL_END' => '',
    'LANG' => 'en',
    'LANGDIR' => "{$srcdir}/en",
    'PHP_BUILD_DATE' => date('Y-m-d'),
    'ENCODING' => 'utf-8',
    'FORCE_DOM_SAVE' => 'no',
    'PARTIAL' => 'no',
    'DETAILED_ERRORMSG' => 'no',

    // Junk to make the old scripts (file-entities.php and missing-entities.php) cooperative
    'PHP_SOURCE' => 'no',
    'PEAR_SOURCE' => 'no',
    'PECL_SOURCE' => 'no',
    'EXT_SOURCE' => 'no',
    'CYGWIN' => '0',
    'WINJADE' => '0',
    'NSGMLS' => 'no',
    'SP_OPTIONS' => 'SP_ENCODING=XML SP_CHARSET_FIXED=YES',
); // }}}

$ac = $acd;

$srcdir_dependant_settings = array( 'INIPATH', 'LANGDIR' );
$overridden_settings = array();

foreach ($_SERVER['argv'] as $k => $opt) { // {{{
    $parts = explode('=', $opt, 2);
    if (strncmp($opt, '--enable-', 9) == 0) {
        $o = substr($parts[0], 9);
        $v = 'yes';
    } else if (strncmp($opt, '--disable-', 10) == 0 || strncmp($opt, '--without-', 10) == 0) {
        $o = substr($parts[0], 10);
        $v = 'no';
    } else if (strncmp($opt, '--with-', 7) == 0) {
        $o = substr($parts[0], 7);
        $v = isset($parts[1]) ? $parts[1] : 'yes';
    } else if (strncmp($opt, '--', 2) == 0) {
        $o = substr($parts[0], 2);
        $v = isset($parts[1]) ? $parts[1] : 'yes';
    } else if ($opt[0] == '-') {
        $o = $opt[1];
        $v = substr($opt, 2);
    } else {
        continue;
    }
    
    $overridden_settings[] = strtoupper($o);
    switch ($o) {
        case 'h':
        case 'help':
            usage();
            exit();

        case 'V':
        case 'version':
            // Version/revision is always printed out
            exit();

        case 'q':
        case 'quiet':
        case 'silent':
            $ac['quiet'] = $v;
            break;

        case 'srcdir':
            foreach ($srcdir_dependant_settings as $s) {
                if (!in_array($s, $overridden_settings)) {
                    $ac[$s] = $v . substr($ac[$s], strlen($ac['srcdir']));
                }
            }
            $ac['srcdir'] = $v;
            break;

        case 'force-dom-save':
            $ac['FORCE_DOM_SAVE'] = $v;
            break;

        case 'chm':
            $ac['CHMENABLED'] = $v;
            break;

        case 'internals':
            $ac['INTERNALSENABLED'] = $v;
            break;

        case 'php':
            $ac['PHP'] = $v;
            break;

        case 'inipath':
            $ac['INIPATH'] = $v;
            break;

        case 'lang':
            $ac['LANG'] = $v;
            break;

        case 'partial':
            if ($v == "yes") {
                if (isset($_SERVER['argv'][$k+1])) {
                    $val = $_SERVER['argv'][$k+1];
                    errbox("TYPO ALERT: Didn't you mean --{$o}={$val}?");
                } else {
                    errbox("TYPO ALERT: --partial without a chunk ID?");
                }
            }

            $ac['PARTIAL'] = $v;
            break;

        case 'xml-details':
            $ac['DETAILED_ERRORMSG'] = $v;
            break;
        
        default:
            echo "WARNING: Unknown option '{$o}'!\n";
            break;
    }
} // }}}

checking('for source directory');
if (!file_exists($ac['srcdir']) || !is_dir($ac['srcdir']) || !is_writable($ac['srcdir'])) {
    checkerror("Source directory doesn't exist or can't be written to.");
}
$ac['SRCDIR'] = $ac['srcdir'];
$ac['WORKDIR'] = $ac['srcdir'];
checkvalue($ac['srcdir']);

checking('whether to save an invalid .manual.xml');
checkvalue($ac['FORCE_DOM_SAVE']);

checking('whether to include CHM');
$ac['CHMONLY_INCL_BEGIN'] = ($ac['CHMENABLED'] == 'yes' ? '' : '<!--');
$ac['CHMONLY_INCL_END'] = ($ac['CHMENABLED'] == 'yes' ? '' : '-->');
checkvalue($ac['CHMENABLED']);

checking("whether to include internals documentation");
$ac['INTERNALS_EXCL_BEGIN'] = ($ac['INTERNALSENABLED'] == 'yes' ? '' : '<!--');
$ac['INTERNALS_EXCL_END'] = ($ac['INTERNALSENABLED'] == 'yes' ? '' : '-->');
checkvalue($ac['INTERNALSENABLED']);

checking("for PHP executable");
if ($ac['PHP'] == '' || $ac['PHP'] == 'no') {
    $ac['PHP'] = find_file($php_bin_names);
} else if (file_exists($cygwin_php_bat)) {
    $ac['PHP'] = $cygwin_php_bat;
}

if ($ac['PHP'] == '') {
    checkerror("Could not find a PHP executable. Use --with-php=/path/to/php.");
}
if (!file_exists($ac['PHP']) || !is_executable($ac['PHP'])) {
    checkerror("PHP executable is invalid - how are you running configure? " .
               "Use --with-php=/path/to/php.");
}
$ac['PHP'] = abspath($ac['PHP']);
checkvalue($ac['PHP']);

checking("for PHP INI path");
if ($ac['INIPATH'] != '' && $ac['INIPATH'] != 'no') {
    if (!file_exists($ac['INIPATH']) || !is_readable($ac['INIPATH'])) {
        checkerror("INI path doesn't exist or isn't readable.");
    }
    $ac['INIPATH'] = abspath($ac['INIPATH']);
}
checkvalue($ac['INIPATH']);

checking("for language to build");
if ($ac['LANG'] == '' || $ac['LANG'] == 'no') {
    checkerror("Using '--with-lang=' or '--without-lang' is just going to cause trouble.");
} else if ($ac['LANG'] == 'yes') {
    $ac['LANG'] = 'en';
}
checkvalue($ac['LANG']);

checking("whether the language is supported");
$LANGDIR = "{$ac['srcdir']}/{$ac['LANG']}";
if (!file_exists($LANGDIR) || !is_readable($LANGDIR)) {
    checkerror("No language directory found.");
}

$ac['LANGDIR'] = basename($LANGDIR);
checkvalue("yes");

checking("for partial build");
checkvalue($ac['PARTIAL']);

checking('whether to enable detailed XML error messages');
checkvalue($ac['DETAILED_ERRORMSG']);


// Do NOT add a commandline setting for this. We only support $ac[ 'NSGMLS' ] to
//  keep missing-entities.php.in working.
checking("for nsgmls");
if (!is_windows() && ($nsgmls = abspath(find_file($nsgmls_bin_names))) != '')
    $ac['NSGMLS'] = $nsgmls;
checkvalue($ac['NSGMLS']);

// We shouldn't be globbing for this. autoconf requires you to tell it which files to use, we should do the same
// Notice how doing it this way results in generating less than half as many files.
$infiles = array(
    'Makefile.in',
    'manual.xml.in',
    'entities/version.ent.in',
    'scripts/file-entities.php.in',
    'scripts/missing-entities.php.in'
);

foreach ($infiles as $in) {
    $in = chop("{$ac['srcdir']}/{$in}");

    $out = substr($in, 0, -3);
    echo "Generating {$out}... ";
    if (generate_output_file($in, $out, $ac)) {
        echo "done\n";
    } else {
        echo "fail\n";
        errors_are_bad(117);
    }
}

globbetyglob("{$ac['srcdir']}/scripts", 'make_scripts_executable');
file_put_contents("{$ac['srcdir']}/entities/phpweb.ent", '');

$ini = ($ac['INIPATH'] != '' && $ac['INIPATH'] != 'no') ? " -c \"{$ac['INIPATH']}\"" : '';
$redir = ($ac['quiet'] == 'yes') ? " > /dev/null" : '';

quietechorun("\"{$ac['PHP']}\"{$ini} -q \"{$ac['srcdir']}/scripts/file-entities.php\"{$redir}");

// just unlink() the two files we know need to be removed. if you update missing-entities.php, update this
$missing_stuff = array(
    "entities/missing-entities.ent",
    "entities/missing-ids.xml"
);
foreach ($missing_stuff as $relpath) {
    if (file_exists("{$ac['srcdir']}/{$relpath}")) {
        echo "Removing {$ac['srcdir']}/{$relpath}\n";
        if (@unlink("{$ac['srcdir']}/{$relpath}") === FALSE) {
            echo "Failed to remove {$relpath}. Check your permissions.\n";
            errors_are_bad(178);
        }
    }
}
// We don't need missing-entities to be run unless we're not rendering English
if ($ac['LANG'] != 'en') {
    quietechorun("\"{$ac['PHP']}\"{$ini} -q \"{$ac['srcdir']}/scripts/missing-entities.php\"{$redir}");
} else {
    // Stick some empty data in there
    file_put_contents("{$ac['srcdir']}/entities/missing-entities.ent", '<'.'?xml version="1.0" encoding="utf-8"?'.">\n");
    file_put_contents("{$ac['srcdir']}/entities/missing-ids.xml", '<'.'?xml version="1.0" encoding="utf-8"?'.">\n");
}
echo "Loading and parsing manual.xml... ";
flush(STDOUT);

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$compact = defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0;
$LIBXML_OPTS = LIBXML_NOENT | LIBXML_NSCLEAN | $compact;

// realpath() is important: omitting it causes severe performance degradation
// and doubled memory usage on Windows.
$didLoad = $dom->load(realpath("{$ac['srcdir']}/manual.xml"), $LIBXML_OPTS);

// Check if the XML was simply broken, if so then just bail out
if ($didLoad === false) {
    echo "failed.\n";
    print_xml_errors();
    errors_are_bad(1);
}

echo "done.\n";
echo "Validating manual.xml... ";
flush(STDOUT);

$dom->xinclude();

if ($ac['PARTIAL'] != '' && $ac['PARTIAL'] != 'no') { // {{{
    $dom->validate(); // we don't care if the validation works or not
    $node = $dom->getElementById($ac['PARTIAL']);
    if (!$node) {
        echo "failed.\n";
        echo "Failed to find partial ID in source XML: {$ac['PARTIAL']}\n";
        errors_are_bad(1);
    }
    if ($node->tagName !== 'book' && $node->tagName !== 'set') {
        // this node is not normally allowed here, attempt to wrap it
        // in something else
        $parents = array();
        switch ($node->tagName) {
            case 'refentry':
                $parents[] = 'reference';
                // Break omitted intentionally
            case 'part':
                $parents[] = 'book';
                break;
        }
        foreach ($parents as $name) {
            $newNode = $dom->createElement($name);
            $newNode->appendChild($node);
            $node = $newNode;
        }
    }
    $set = $dom->documentElement;
    $set->nodeValue = '';
    $set->appendChild($dom->createElement('title', 'PHP Manual (Partial)')); // prevent validate from complaining unnecessarily
    $set->appendChild($node);

    $filename = "{$ac['srcdir']}/.manual.{$ac['PARTIAL']}.xml";
    $dom->save($filename);
    echo "done.\n";
    echo "Partial manual saved to {$filename}. To build it, run 'phd -d {$filename}'\n";
    exit(0);
} // }}} 

$mxml = "{$ac['srcdir']}/.manual.xml";
if ($dom->validate()) {
    echo "done.\n";
    echo "\nAll good. Saving .manual.xml... ";
    flush(STDOUT);
    $dom->save($mxml);

    echo "done.\n";
    echo "All you have to do now is run 'phd -d {$mxml}'\n";
    echo "If the script hangs here, you can abort with ^C. (Run `nice php configure.php` next time!)\n";
    exit(0); // Tell the shell that this script finished successfully.
} else {
    echo "failed.\n";
    echo "\nThe document didn't validate, ";

    // Allow the .manual.xml file to be created, even if it is not valid.
    if ($ac['FORCE_DOM_SAVE'] == 'yes') { 
        echo "writing .manual.xml anyway, and ";
        $dom->save($mxml);
    }

    if ($ac['DETAILED_ERRORMSG'] == 'yes') {
        echo "trying to figure out what went wrong...\n";
        echo "(This could take awhile. If you experience segfaults here, try again with --disable-xml-details)\n";
        libxml_clear_errors(); // Clear the errrors, they contain incorrect filename&linenr

        $dom->load("{$ac['srcdir']}/manual.xml", $LIBXML_OPTS | LIBXML_DTDVALID);
        print_xml_errors();
    } else {
        echo "here are the errors I got:\n";
        echo "(If this isn't enough information, try again with --enable-xml-details)\n";
        print_xml_errors(false);
    }

    errors_are_bad(1); // Tell the shell that this script finished with an error.
}
?>

