<?php
/*
# +----------------------------------------------------------------------+
# | PHP Version 4                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2003 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP licence,      |
# | that is bundled with this package in the file LICENCE and is         |
# | avalible through the world wide web at                               |
# | http://www.php.net/license/2_02.txt.                                 |
# | If you did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world wide web, please send a note to          |
# | license@php.net so we can mail you a copy immediately.               |
# +----------------------------------------------------------------------+
# | Authors:    Mitja Slenc <mitja@php.net>                              |
# |             Gabor Hojtsy <goba@php.net>                              |
# +----------------------------------------------------------------------+
# 
# $Id$
*/

$XML_REF_ROOT = "../../en/reference/";
$FUNCTIONS    = array();

if ($dh = @opendir($XML_REF_ROOT)) {
    while (($file = readdir($dh)) !== FALSE) {
        if (is_dir($XML_REF_ROOT . $file) && !in_array($file, array(".", "..", "CVS"))) {
            get_function_files($XML_REF_ROOT . $file);
        }
    }
    closedir($dh);
} else {
    die("Unable to find phpdoc XML files");
}

sort($FUNCTIONS);
fwrite(fopen("funclist.txt", "w"), implode("\n", $FUNCTIONS)."\n");

function get_function_files($dir) {
    global $FUNCTIONS;
    if ($dh = @opendir($dir . "/functions")) {
        while (($file = readdir($dh)) !== FALSE) {
            if (fnmatch("*.xml", $file)) {
                $FUNCTIONS[] = str_replace(array(".xml", "-"), array("", "_"), $file);
            }
        }
        closedir($dh);
    } else {
        die("Unable to find phpdoc XML files in $dir folder");
    }
}

?>
