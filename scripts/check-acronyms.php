<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
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

define('M_SPECIFIC', 1);  // display complete informations about specific acronyms
define('M_GLOBAL',   2);  // display brief informations about every acronyms
define('M_VERBOSE',  3);  // display complete informations about every acronyms

if ($_SERVER['argc'] == 2 &&
      in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?')) 
      || 
      $_SERVER['argc'] < 2) {

    echo "Display acronyms statistics\n\n";
    echo "Usage:      {$_SERVER['argv'][0]} [-v] <reference path>\n";
    echo "Usage:      {$_SERVER['argv'][0]} -i <acronym> [acronym...]\n";
    echo "            --help, -help, -h, -?      - to get this help\n";
    echo "            -v                         - verbose\n";
    
    exit(1);

}

$requested_acronyms = array();

// select mode
if ($_SERVER['argv'][1] !== '-i') {

    if ($_SERVER['argv'][1] === '-v' && isset($_SERVER['argv'][2])) {
    
        $mode       = M_VERBOSE;
        $start_path = $_SERVER['argv'][2];
        
    } else {
    
        $mode       = M_GLOBAL;
        $start_path = $_SERVER['argv'][1];
        
    }

} else if (isset($_SERVER['argv'][2])) {

    $mode       = M_SPECIFIC;
    $start_path = 'en/';

    $requested_acronyms = array_slice($_SERVER['argv'], 2);

} else {

    echo "ERROR: Bad argument given\n";
    exit(1);

}
// }}}


// gather every informations on acronyms
$acronyms_infos = array();


// now look up into the source
$options = array('ignore'       => array('.', '..', 'CVS'),
                 'callback'     => 'acronymDetails',
                 'userdata'     => &$acronyms_infos,
                 'extensions'   => array('xml'));
 
filesWalk($start_path, $options);


// complete with information about definition
$content = file_get_contents('entities/acronyms.xml');

if (preg_match_all('#<term>(.+?)</term>\s+<listitem>\s+<simpara>(.+?)</simpara#', $content, $matches)) {

    foreach($matches[1] as $id => $acronym) {
    
        if (!isset($acronyms_infos[$acronym])) {
            $acronyms_infos[$acronym] = array('defined'     => true,
                                              'description' => $matches[2][$id],
                                              'locations'   => array());
                                          
        } else {
            $acronyms_infos[$acronym]['defined']     = true;
            $acronyms_infos[$acronym]['description'] = $matches[2][$id];
        }
        
    }
    
}


// display the information depending on the mode

if ($mode == M_GLOBAL || $mode == M_VERBOSE) {
    
    $unUsed          = array();
    $used            = array();
    $defined         = array();
    $usedNnotdefined = array();
    $total_occs    = 0; 
    foreach($acronyms_infos as $name => $acronym) {

        if ($acronym['defined']) {
            $defined[$name] = $acronym;
        }
        
        if (!empty($acronym['locations'])) {
            //used
            
            if (!$acronym['defined']) {
                $usedNnotdefined[$name] = $acronym;
            }

            $used[$name] = $acronym;
        }

        $total_occs += count($acronym['locations']);
    }

    $total_used    = count($used);
    $total_all     = count($acronyms_infos);
    $total_defined = count($defined);
    $total_UNNd    = count($usedNnotdefined); 
    $total_uNd     = $total_used-$total_UNNd;
    
    echo "Found $total_occs occurrences of $total_used acronyms in $start_path\n";
    echo " $total_defined acronyms are defined in acronyms.ent\n";
    echo " $total_uNd (".round(100*$total_uNd/$total_used)."%) acronyms used are defined\n";

    if ($total_UNNd) {
        echo " ".$total_UNNd." (".round(100*$total_UNNd/$total_used)."%) are used but not defined:\n\n";
        foreach($usedNnotdefined as $name => $acronym) {
            echo "  $name\n";
        }
    }
    
    if ($mode === M_GLOBAL) {
        echo "\nStats found in $start_path about all acronyms:\n";
    } else {
        echo "\nDefined acronyms are:\n";
    }

    echo "\n";
}

$found = false;

foreach($acronyms_infos as $acronym => $infos) {
    
    // display differs with the mode used
    if ($mode == M_GLOBAL) {
        $occ = count($infos['locations']);
        printf(" %-20s %-60s [%2d occurrences]\n", $acronym,  "($infos[description])", $occ);
    } else if ($mode === M_SPECIFIC || $mode === M_VERBOSE) {
        
        if ($mode === M_VERBOSE && count($infos['locations']) || in_array($acronym, $requested_acronyms)) {

            echo " $acronym ($infos[description]):\n";
            $occ = count($infos['locations']);
            echo "  - ".$occ." occurrence".($occ > 1 ? 's':'')."\n";

            foreach($infos['locations'] as $location) {
                printf("    %-30s [line %d]\n", $location[1], $location[0]);
            }
            echo "\n";
            
            $found = true;
        }
        
    }
}

if (!$found && $mode === M_SPECIFIC) {
    echo "ERROR: No informations were found about requested acronyms. It may not exist.\n";
}


// {{{ acronymDetails($file, $userdata)
/**
 * Callback looking for informations about specific acronyms
 */

function acronymDetails($file, &$userdata) {
    $content = file_get_contents($file);
    
    if(preg_match_all('#<acronym>([^>]+)</acronym>#', $content, $matches, PREG_OFFSET_CAPTURE)) {
        
        foreach($matches[1] as $acronym) {
            
            $line = substr_count(substr($content, 0, $acronym[1]), "\n")+1;
            
            if (!isset($userdata[$acronym[0]])) {
                $userdata[$acronym[0]] = array('defined'     => false,
                                               'description' => '<undefined>',
                                               'locations'   => array(array($line, $file)));
            } else {
                $userdata[$acronym[0]]['locations'][]         = array($line, $file);
            }
            
        }
    }
}

// }}}

// {{{ filesWalk($directory, $options)
/**
 * General function used to walk through files and call a function when 
 * required.
 */

function filesWalk($directory, $options) {

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
