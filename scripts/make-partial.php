#!/usr/bin/env php
<?php
/** vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
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

require_once "Console/Getopt.php";
$console = new Console_Getopt;
$args = $console->getopt($console->readPHPArgv(), array(), 
                         array("format=", "include="));

if (!function_exists("readline")) {
    echo "Error: The readline extension could not be found!";
    exit(1);
}

$file = file("manual.xml.in");
if (!$file) {
    echo "Error: Unable to read manual.xml.in!";
    exit(1);
}

copy("manual.xml.in", "manual.xml.in.partial-backup");
register_shutdown_function("restoreFile");

// {{{ gather arguments

$format = "html";
$sections = array();

foreach ($args[0] as $arg) {
    if ($arg[0] == "--format") {
        $format = $arg[1];
    } elseif ($arg[0] == '--include') {
        $sections[] = $arg[1];
    }
}

// }}}


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
            if ($includePart = in_string($sections, $matches[1])) {
                echo "YES\n";
            } else {
                echo "NO\n";
            }
        } else {
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
                if ($include = in_string($sections, $matches[2])) {
                    echo "YES\n";
                } else {
                    echo "NO\n";
                }
            } else {
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
function restoreFile() {
    if (!is_file("manual.xml.in.partial-backup")) {
        return;
    }

    rename("manual.xml.in.partial-backup", "manual.xml.in");
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
 * $needle (array) is in $haystack?
 *
 */
function in_string($needle, $haystack)
{
    foreach ((array) $needle AS $n) {
        if (stripos($haystack, $n) !== false) {
            return true;
        }
    }
    return false;
}

// }}}
