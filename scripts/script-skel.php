<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
+----------------------------------------------------------------------+
| PHP Documentation Site Source Code                                   |
+----------------------------------------------------------------------+
| Copyright (c) 1997-2011 The PHP Group                                |
+----------------------------------------------------------------------+
| This source file is subject to version 3.01 of the PHP license,      |
| that is bundled with this package in the file LICENSE, and is        |
| available at through the world-wide-web at                           |
| http://www.php.net/license/3_01.txt.                                 |
| If you did not receive a copy of the PHP license and are unable to   |
| obtain it through the world-wide-web, please send a note to          |
| license@php.net so we can mail you a copy immediately.               |
+----------------------------------------------------------------------+
| Authors: Etienne Kneuss <colder@php.net>                             |
+----------------------------------------------------------------------+
$Id$
*/

if (PHP_SAPI !== 'cli') {
    echo "This script is ment to be run under CLI\n";
    exit(1);
}

if ($_SERVER['argc'] == 2 &&
      in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?')) 
      || 
      $_SERVER['argc'] < 2) {

    echo "<Description>\n\n";
    echo "Usage:      {$_SERVER['argv'][0]} <path>\n";
    echo "            --help, -help, -h, -?      - to get this help\n";
    die;

}

// Ensure the trailing /
$fullpath_dir = rtrim($_SERVER['argv'][1], '/').'/';

if (!is_dir($fullpath_dir)) {
    echo "ERROR: ($fullpath_dir) is not a directory.\n";
    exit(1);
}


$log = array('nonfiles'  => array(),
             'error'     => array(),
             'rewritten' => array());
    
// Start the processing
list_files($fullpath_dir, '', $log);    



echo count($log['rewritten'])." file(s) have been affected.\n";
if (!empty($log['error'])) {
    echo count($log['error'])." error(s) occured:\n";
    foreach($log['error'] as $error) {
        echo " $error\n";
    }
}


/**
 * List files recursivly and scan them
 *
 * @return bool
 */
function list_files($prefix, $path, &$userdata) 
{
    
    if (is_dir($prefix.$path) && is_resource($handle = @opendir($prefix.$path))) {

        while ($name = readdir($handle)) {
            if (strpos($name, ".xml") !== false) {
                scan_file($prefix, $path.$name, $userdata);
            } else if(is_dir($prefix.$path.$name) && $name !== 'CVS' && $name !== '.' && $name !== '..') {
                list_files($prefix, $path.$name.DIRECTORY_SEPARATOR, $userdata);
            }

        }

        closedir($handle);
        return true;

    } else {
        return false;
    }
    
}

/**
 * Scan files for examples, and insert them
 *
 * @return null
 */
function scan_file($prefix, $path, &$userdata) 
{
    
    if (!is_file($prefix.$path)) {
        $userdata['nonfiles'][] = $path;
        return false;
    }
    
    $content = file_get_contents($prefix.$path);
    echo "scanning $path\n";
    if ($number = preg_match_all('/regex/', $content, $matches)) {
        
        // Process

        $userdata['rewritten'][] = $path;
    }
    
}

