<?php

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
  | Authors : Carola Sammy Kummert <sammywg@php.net>                     |
  +----------------------------------------------------------------------+
*/

error_reporting(E_ALL);
define('VERSION', '$Id$');

if (PHP_SAPI != 'cli') {
    print "This script is for command line use only.\n";
    exit();
}

// define options
define('OPT_HELP', 'h');
define('OPT_DRY',  'n');
define('OPT_VERB', 'v');
define('OPT_PATH', 'p:');
define('OPT_LANG', 'l:');
define('OPT_TMPF', 'f:');
define('OPT_DMMY', 'd:');

$shortoptions = array(
    OPT_PATH  => array(
        'default' => getcwd(),
        'help'    => 'Path where PHP Documentation checkout resides [.]',
    ),
    OPT_TMPF  => array(
        'default' => '/tmp/check_maintainers.tmp',
        'help'    => 'Temporary working copy [/tmp/check_maintainers.tmp]',
    ),
    OPT_DMMY  => array(
        'default' => 'nobody',
        'help'    => 'Dummy maintainer\'s name (range [A-Za-z] only) [nobody]',
    ),
    OPT_LANG  => array(
        'help'    => 'Language to fix',
    ),
    OPT_HELP  => array(
        'help'    => 'Display this help and exit',
    ),
    OPT_DRY   => array(
        'help'    => 'Show what would have been replaced',
    ),
    OPT_VERB  => array(
        'help'    => 'Increase verbosity',
    ),
);

$options = getopt(implode(array_keys($shortoptions)));

// check options
if (isset($options['h'])) {
    fPrintHelp($shortoptions);
    exit();
}

if (!isset($options['l'])) {
    print "No language specified, use -l option followed by language.\n";
    exit();
}

$path = $shortoptions[OPT_PATH]['default'].DIRECTORY_SEPARATOR.$options['l'];
if (isset($options['p'])) {
    $path = realpath($options['p'].DIRECTORY_SEPARATOR.$options['l']);
}

$tmpFile = $shortoptions[OPT_TMPF]['default'];
if (isset($options['f'])) {
    $tmpFile = realpath(dirname($options['f'])).DIRECTORY_SEPARATOR.basename($options['f']);
}

if (!isset($options['n']) && is_string($tmpFile) && !is_writable($tmpFile)) {
    print "Cannot write working copy to {$tmpFile}. Please check path and permissions.\n";
    exit();
}

$dummy = $shortoptions[OPT_DMMY]['default'];
if (isset($options['d'])) {
    if (preg_match('/[^A-Za-z]+/', $options['d'])) {
        print "Dummy user's name contains illegal chars.\n";
        exit();
    }
    $dummy = $options['d'];
}

// get maintainers from language translation table
$xmlMt = fGetValidMaintainers($path);

// traverse through the translation directory and check all files
fCheckTransDir($path);

exit();



/**
 * Output script help
 *
 * @param  array  $shortoptions
 * @return void
 * @todo   implement dynamic help output
 */
function fPrintHelp($shortoptions) {
    exit();
}



/**
 * Get valid maintainers from translation.xml
 *
 * @param  string $path
 * @return array  SimpleXMLElement
 */
function fGetValidMaintainers($path) {
    $xml = @simplexml_load_file($path.DIRECTORY_SEPARATOR.'translation.xml');
    if (is_bool($xml)) {
        print "Wrong path. Check if the entered path is the directory where the CVS checkout resides.\n";
        exit();
    }
    $ns = $xml->getDocNamespaces();
    $xml->registerXPathNamespace('std',$ns['']);
    $xmlMt = $xml->xpath('//std:translators/std:person/@nick');

    return $xmlMt;
}



/**
 * Walk through translation directory
 *
 * @param  string $path
 * @return void
 */
function fCheckTransDir($path) {
    $dir = dir($path);
    $aIgnore = array('.', '..', 'CVS', '.cvsignore');
    while (false !== ($entry = $dir->read())) {
        if (in_array($entry, $aIgnore)) {
            continue;
        }
        if (is_file($dir->path.DIRECTORY_SEPARATOR.$entry)) {
            fCheckMaintainer($dir->path.DIRECTORY_SEPARATOR.$entry);
        } elseif (is_dir($dir->path.DIRECTORY_SEPARATOR.$entry)) {
            fCheckTransDir($dir->path.DIRECTORY_SEPARATOR.$entry);
        }
    }

    return;
}



/**
 * Check given file for maintainer information and reset
 *
 * @param  string $filename
 * @global array  $options
 * @global string $tmpFile
 * @global array  $xmlMt
 * @global string $dummy
 * @return void
 */
function fCheckMaintainer($filename) {
    global $options, $tmpFile, $xmlMt, $dummy;

    $file = fopen($filename, 'r');
    if (!isset($options[OPT_DRY])) {
        $file2 = fopen($tmpFile, 'w+');
    }
    $aMt = array();
    $newMt = false;
    $i = 0;
    while(!feof($file) && $i < 10) {
        $content = fgets($file);
     // if no maintainer information found get next line
        if (0 == preg_match('/<!--.+Maintainer: (\w+).+-->/', $content, $aMt)) {
            if (!isset($options[OPT_DRY])) {
                fwrite($file2, $content);
            }
            ++$i;
            continue;
        }
     // replace maintainer if not in list and not dummy
        if (!in_array($aMt[1], $xmlMt) && $aMt[1] != $dummy) {
            $content = str_replace($aMt[1], $dummy, $content);
            $newMt = true;
        }
        if (!isset($options[OPT_DRY])) {
            fwrite($file2, $content);
        }
        ++$i;
        break;
    }
 // generate some information sets
    if (isset($options[OPT_VERB])) {
        printf("\nII : check %s %u lines\n", $filename, $i);
    }
    if ($i == 10) {
        printf("EE : No maintainer entry in %s\n", $filename);
    } elseif ($newMt) {
        printf("WW : Maintainer %s reset to %s in %s\n", $aMt[1], $dummy, $filename);
    }
 // pass the rest of file directly through temp file
    if (!isset($options[OPT_DRY])) {
        fseek($file, ftell($file));
        fwrite($file2, fread($file, filesize($filename)));
        fclose($file2);
    }
    fclose($file);
 // replaye original file
    if (!isset($options[OPT_DRY])) {
        rename($tmpFile, $filename);
    }

    return;
}

?>