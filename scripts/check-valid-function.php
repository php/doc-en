<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2010 The PHP Group                                |
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
  +----------------------------------------------------------------------+
  | Description: This file parses the manual and outputs all erroneous   |
  |              <function> tag usage.                                   |
  +----------------------------------------------------------------------+

*/

/* path to phpdoc CVS checkout. if this file is in the scripts/ directory
 * then the value below will be correct!
 */
$phpdoc = '../';

/* english! */
$lang = 'en';

/* initialize array and declare some language constructs */
$funcs = array( 'include'      => true,
                'include_once' => true,
                'require'      => true,
                'require_once' => true,
                'return'       => true,
               );

$total = 0;

/* recursive glob() with a callback function */
function globbetyglob($globber, $userfunc)
{
    foreach (glob("$globber/*") as $file) {
        if (is_dir($file)) {
            globbetyglob($file, $userfunc);
        }
        else {
            call_user_func($userfunc, $file);
        }
    }
}

/* make a function list from files in the functions/ directories */
function make_func_list($file)
{
    global $funcs;

    if (fnmatch("*/reference/*/functions/*.xml", $file)) {
        $func = strtolower(str_replace(array('-', '.'), '_', substr(basename($file), 0, -4)));
        $funcs[$func] = true;
    }
}

/* find all <function> tags and report invalid functions */
function parse_file($file)
{
    global $funcs, $phpdoc, $lang, $total;

    /* ignore old functions directory */
    if (fnmatch("$phpdoc/$lang/functions/*", $file))
        return;

    $f = file_get_contents($file);

    if ($f != '') {
        if (preg_match_all('|<function>(.*?)</function>|s', $f, $m)
            && is_array($m)
            && is_array($m[1]))
        {
            foreach ($m[1] as $func) {
                $func = strtolower(str_replace(array('::', '->'), '_', trim($func)));
                if ($funcs[$func] !== true) {
                    $total++;
                    $fileout = substr($file, strlen($phpdoc) + 1);

                    printf("%-60.60s  <function>$func</function>\n", $fileout);
                }
            }
        }
    }
}

echo "Building a list of functions...\n";
globbetyglob("$phpdoc/$lang", 'make_func_list');
echo 'List complete. ' . count($funcs) . " functions.\n";

echo "Checking the manual for <function> tags that contain invalid functions...\n";
globbetyglob("$phpdoc/$lang", 'parse_file');
echo "Found $total occurrences.\n";
?>
