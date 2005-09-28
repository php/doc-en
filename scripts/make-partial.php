#!/usr/bin/env php
<?php
/** vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
 *
 * Script to trigger partial builds of the PEAR manual
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @author    Martin Jansen <mj@php.net>
 * @copyright 2005 The PEAR Group
 * @version   CVS: $Id$
 */

// NOTE: originally from peardoc:/make-partial.php ;
// these files should be kept in sync

if (substr(PHP_VERSION, 0, 1) == "4") {
    require_once "PHP/Compat.php";
    $components = PHP_Compat::loadVersion('5.0.0');
}

require_once "Console/Getopt.php";
$console = new Console_Getopt;
$args = $console->getopt($console->readPHPArgv(), array(), 
                         array("format=", "include=", "help"));

// {{{ gather arguments

$format = "html";
$sections = array();

$incflag = false;
foreach ($args[0] as $arg) {
    if ($arg[0] == "--help") {
        showHelp();
        exit(0);
    } elseif ($arg[0] == "--format") {
        $format = $arg[1];
    } elseif ($arg[0] == '--include') {
        $sections[] = $arg[1];
        $incflag = true;
    }    
}

if ($incflag) {
    // collect other space delimited names as section names
    foreach ($args[1] as $arg) {
        $sections[] = $arg;
    }
}

// }}}

$hasReadline = true;
if (!function_exists("readline")) {
    $hasReadline = false;
    echo "Warning: The readline extension could not be found!\n";
    if (count($sections) == 0) {
        showHelp();
        echo "Exiting because no --include parameters were specified.\n";
        exit(1);
    }
}

// recover manual.xml.in if the script was terminated unexpectedly
restoreFile();

copy("manual.xml.in", "manual.xml.in.partial-backup");
register_shutdown_function("restoreFile", filemtime("manual.xml.in"));

$file = file("manual.xml.in");
if (!$file) {
    echo "Error: Unable to read manual.xml.in!";
    exit(1);
}

$newFile = "";
$partStack = array();
$includePart = true;
$notInPart = true;

/**
 * Loop through the file and build a new file depending on the users
 * choice.
 */
foreach ($file as $line) {
    // <part id="foo">
    if (preg_match("/<part id=\"([a-z-]+)\">/", $line, $matches)) {
        $inPart = true;

        if ($sections) {
            echo "Including ". $matches[1] ."? ";
            if ($includePart = inString($sections, $matches[1])) {
                echo "YES\n";
            } else {
                echo "NO\n";
            }
        } else if ($hasReadline) {
            $include = readline("Include " . $matches[1] . "? [NO] ");
            $includePart = evaluate($include);
        }

        if ($includePart == true) {
            $newFile .= $line;
        }

        continue;
    }

    // </part>
    if (preg_match("/<\/part>/", $line)) {
        if (count($partStack) > 0) {
            $newFile .= implode("", $partStack);
            $partStack = array();
        }

        if ($includePart == true) {
            $newFile .= $line;
        }
        $includePart = false;
        $inPart = false;

        continue;
    }

    // <title>
    if ($inPart == true && $includePart && preg_match("/<title/", $line)) {
        $partStack[] = $line;
        continue;
    }

    // the rest
    if ($inPart == true) {
        if ($includePart == false) {
            continue;
        }
        
        if (preg_match("/(\s\t)*&([a-z0-9\.-]+);/", $line, $matches)) {

            if ($sections) {
                echo "Including ". $matches[2] ."? ";
                if ($include = inString($sections, $matches[2])) {
                    echo "YES\n";
                } else {
                    echo "NO\n";
                }
            } else if ($hasReadline) {
                $include = evaluate(readline("Include " . $matches[2] . "? [NO] "));
            }
            
            if ($include == true) {
                $partStack[] = $line;
            }
        }
    } else {
        $newFile .= $line;
    }
}

file_put_contents("manual.xml.in", $newFile);

// {{{ Run the build scripts

$cmd = "make " . $format;
passthru($cmd);

// }}}
// {{{ Helper functions

/**
 * Restores the original manual.xml.in file
 */
function restoreFile($savedmtime = null) {
    if (!is_file("manual.xml.in.partial-backup")) {
        return;
    }

    unlink("manual.xml.in");
    rename("manual.xml.in.partial-backup", "manual.xml.in");
    if ($savedmtime) touch("manual.xml.in", $savedmtime);
}

/**
 * Evaluates the return value of readline()
 *
 * If the first parameter is either "y" or "yes" the method will
 * return true. Otherwise false.
 */
function evaluate($str) {
    if ($str == 'y' || $str == "yes") {
        return true;
    }
    return false;
}

/**
 * Checks if one element of the first parameter is part of the second parameter
 *
 * @param  array List of needles
 * @param  string Haystack
 * @return boolean True if one of the needles is in the haystack,
 *                 false otherwise.
 */
function inString($needle, $haystack)
{
    foreach ((array) $needle as $n) {
        if (stripos($haystack, $n) !== false) {
            return true;
        }
    }
    return false;
}

/**
 * Prints a usage notice for the script
 *
 * @return void
 */
function showHelp()
{
    echo "Usage: make-partial.php [--format <format>] [--include <section1>] [--include <section2>] ...\n";
    echo "       make-partial.php --help\n";
    echo "\n";
    echo "  --format <format>    Which format to build. Can be one of 'html', 'pearweb'.\n";
    echo "                       Default is 'html'.\n";
    echo "  --include <section>  Automatically include certain sections of the manual\n";
    echo "                       without asking for them explicitely.  This also works\n";
    echo "                       on setups where the readline extension is not available.\n";
    echo "  --help               Prints this help text.\n\n";
}

// }}}
