<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 2005 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Etienne Kneuss <colder@php.net>                          |
  +----------------------------------------------------------------------+
 
  $Id$
*/
// {{{ header, sanity cheks
if (PHP_SAPI !== 'cli') {
    echo "This script is ment to be run under CLI\n";
    exit(1);
}

define('M_BRIEF',    1); // display a brief summary of the actions
define('M_VERBOSE',  2); // display what files have been affected

if ($_SERVER['argc'] == 2 &&
      in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?')) 
      || 
      $_SERVER['argc'] < 2) {

    echo "Generate error level links from <constant>\n\n";
    echo "Usage:      {$_SERVER['argv'][0]} [-b] <reference path>\n";
    echo "            --help, -help, -h, -?      - to get this help\n";
    echo "            -b                         - brief\n";
    exit;

}

if ($_SERVER['argv'][1] === '-b' && isset($_SERVER['argv'][2])) {
    $start_path = $_SERVER['argv'][2];
    define('MODE', M_BRIEF);
} else {
    $start_path = $_SERVER['argv'][1];
    define('MODE', M_VERBOSE);
}


// }}}

$errorLevels = array('E_ERROR',
                     'E_NOTICE',
                     'E_WARNING',
                     'E_PARSE',
                     'E_CORE_ERROR',
                     'E_CORE_WARNING',
                     'E_COMPILE_ERROR',
                     'E_COMPILE_WARNING',
                     'E_USER_ERROR',
                     'E_USER_WARNING',
                     'E_USER_NOTICE',
                     'E_STRICT',
                     'E_RECOVERABLE_ERROR',
                     'E_ALL');

// now look up into the source
$options = array('ignore'       => array('.', '..', 'CVS'),
                 'callback'     => 'modifyErrorConstants',
                 'userdata'     => array('log' => array(), 'errorlevels' => $errorLevels),
                 'extensions'   => array('xml'));
 
filesWalk($start_path, $options);

$total_occs  = 0;
$total_files = count($options['userdata']['log']);
$max_occs    = 0;
$max_len     = 0;
foreach($options['userdata']['log'] as $infos) {
    $total_occs += $infos['occurences'];
    $max_occs = $infos['occurences']   > $max_occs ? $infos['occurences']  : $max_occs;
    $max_len  = strlen($infos['file']) > $max_len ? strlen($infos['file']) : $max_len;
}

echo "Affected $total_files files (found $total_occs occurences).\n";

if (MODE === M_VERBOSE && $total_files) {
    echo "Here is a list of affected files:\n";

    foreach($options['userdata']['log'] as $infos) {
        printf("  %-".$max_len."s (%".strlen($max_occs)."d occurences)\n",$infos['file'], $infos['occurences']);
    }
}


// {{{ modifyErrorConstants($file, $userdata)
/**
 * Callback looking for informations about specific acronyms
 */

function modifyErrorConstants($file, &$userdata) {
    $content = file_get_contents($file);
    
    $count = 0;
    
    $new = preg_replace_callback('#<constant>('.implode('|',$userdata['errorlevels']).')</constant>#i', 'constantsToLinks', $content , -1, $count);

    if($count) {
        // found one, afect the file
        $userdata['log'][] = array('file' => $file, 'occurences' => $count);

        file_put_contents($file, $new);
    }
}

// }}}

// {{{ constantsToLinks($matches)
/**
 * modifying error constants to links
 */

function constantsToLinks($match) {
    return sprintf('<link linkend="%s">%s</link>', str_replace('_', '-', strtolower($match[1])), $match[1]); 
}

// }}}

// {{{ filesWalk($directory, $options)
/**
 * General function used to walk through files and call a function when 
 * required.
 */

function filesWalk($directory, &$options) {

    $options += array('ignore'       => array('.', '..', 'CVS'),
                      'callback'     => null,
                      'extensions'   => array('xml'),
                      'userdata'     => array()); 

    if (is_null($options['callback'])) {
        return false;
    }

    $directory = rtrim($directory, '/');

    if (!is_dir($directory)) {
        echo "ERROR: ($directory) is not a directory.\n";
        return false;
    }

    $dh = opendir($directory);

    if (!$dh) {
        echo "ERROR: ($directory) would not open.\n";
        return false;
    }

    while(false !== ($file = readdir($dh))) {

        $path = $directory.DIRECTORY_SEPARATOR.$file;
        $ext  = pathinfo($file, PATHINFO_EXTENSION);

        if (in_array($file, $options['ignore'])) {
            continue;
        }
        
        if (is_dir($path)) {
            filesWalk($path, $options);
        } elseif(in_array($ext, $options['extensions'])) {
            call_user_func_array($options['callback'], array($path, 
                                                             &$options['userdata']));
        }
    }
    
    return true;
}
// }}}
?>
