#!/usr/bin/php -q
<?php 
/*
# +----------------------------------------------------------------------+
# | PHP Version 4                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2002 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP licience,     |
# | that is bundled with this package in the file LICENCE and is         |
# | avalible through the world wide web at                               |
# | http://www.php.net/license/2_02.txt.                                 |
# | If uou did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world wide web, please send a note to          |
# | license@php.net so we can mail you a copy immediately                |
# +----------------------------------------------------------------------+
# | Authors:    Gabor Hojtsy <goba@php.net>                              |
# +----------------------------------------------------------------------+
*/

set_time_limit(0);
ob_implicit_flush();

if ($argc < 2 || $argc > 3) { ?>
Deletes the HTML !DOCTYPE lines from 'phpweb_xsl' generated files

  Usage:
  <?=$argv[0]?> <dir>

  <dir> is the folder, where the phpweb_xsl output
  files are located. The files will be rewritten to
  get the !DOCTYPE removed.

<?php
    exit;
}

echo "Starting phpweb_xsl fix\n";

// Strip of any ending slash
$startdir = preg_replace('!/+$!', '', $argv[1]);

// Check folder
if (!is_dir($startdir)) {
    die("ERROR: The first parameter is not a directory\n");
}

// Try to open folder
$dh = opendir($startdir);
if (!$dh) { die("ERROR: Unable to open directory\n"); }

// For all the files
while (($filename = readdir($dh)) !== FALSE) {
    
    $fullname = "$startdir/$filename";
    
    // If this is a php file
    if (preg_match("!.php$!", $fullname) && is_file($fullname)) {
        $contents = file($fullname);
        
        // If !DOCTYPE is not found, skip file rewrite
        if (strpos($contents[0], "<!DOCTYPE") === FALSE) { continue; }
        
        // Otherwise, rewrite the contents of the
        // file, skiping the first line
        $fp = fopen($fullname, "w");
        if (!$fp) { die("ERROR: unable to open $fullname for writing\n"); }
        fwrite($fp, join("", array_slice($contents, 1)));
        fclose($fp);
    }
}

closedir($dh);

echo "SUCCESS: !DOCTYPE stripout finished\n";
