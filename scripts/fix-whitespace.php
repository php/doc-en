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
  | Authors:    Philip Olson <philip@php.net>                            |
  |             Etienne Kneuss <colder@php.net>                          |
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

    echo "Prepare documentation for the new style (Whitespace Fix)\n\n";
    echo "Usage:      {$_SERVER['argv'][0]} <path_to_extension>\n";
    echo "            --help, -help, -h, -?      - to get this help\n";
    die;

}

// We only deal with the functions directory
$fullpath_dir = rtrim($_SERVER['argv'][1], '/') . '/functions/';

if (!is_dir($fullpath_dir)) {
    echo "ERROR: ($fullpath_dir) is not a directory.\n";
    exit(1);
}

$dh  = opendir($fullpath_dir);
if (!$dh) {
    echo "ERROR: ($fullpath_dir) would not open.\n";
    exit(1);
}

$log = array('nonfiles'  => array(),
             'newstyle'  => array(),
             'error'     => array(),
             'rewritten' => array());


while (false !== ($file = readdir($dh))) {
    
    $fullpath_file = $fullpath_dir . $file;
    
    if (!is_file($fullpath_file)) {
            $log['nonfiles'][] = $file;
            continue;
    }
    
    $parts = pathinfo($fullpath_file);
    
    if (isset($parts['extension']) && $parts['extension'] !== 'xml') {
            continue;
    }
    
    $lines = file($fullpath_file);
    $hash  = sha1(implode('', $lines));
    
    $tmp   = '';

    
    // start a state machine through the lines
    
    $states = array('in_comment'          => false,
                    'front_spaces'        => 0,
                    'refpurpose_lines'    => array(),
                    'metsys_indent_diff'  => 0,
                    'methodparam_lines'   => array(),
                    );
    
    foreach ($lines as $key => $line) {
            
            do {
                
                // WS should not change inside example code
                if (preg_match('#</?programlisting|</?screen#', $line)) {
                
                    $states['in_comment'] = !$states['in_comment'];
                    
                } else if ($states['in_comment']) {
                
                    break;
                    
                }
                
                // remove front spaces
                if (preg_match('#^([ ]+)<refentry xml:id=(?:"|\')function.#', $line, $match)) {
                    $states['front_spaces'] = strlen($match[1]);

                    if ($states['front_spaces'] !== 2) {
                        // ask for a confirmation if there is not 2 spaces:
                        echo "Strange indenting  (".(int)$states['front_spaces']." spaces) has been encountered in $fullpath_file\n";
                        
                        $number = null;
                        while ($number > $states['front_spaces'] || $number === null) {
                            
                            if($number > $states['front_spaces']) {
                                echo "ERROR: Number out of range.\n";
                            }
                            echo "How many spaces would you like to remove? (0-".(int)$states['front_spaces'].")\n";
                            fscanf(STDIN, "%d\n", $number);
                        }
                        
                        $states['front_spaces'] = $number;
                    }
                    
                }
                
                if ($states['front_spaces']) {
                    $line = preg_replace('#^[ ]{'.(int)$states['front_spaces'].'}#', '', $line);
                }
                
                // WS on refpurpose (put refpurpose on its own line)
                
                
                if (preg_match('#<refpurpose>\s$#', $line)) {
                    
                    $line = rtrim($line);
                    $states['refpurpose_lines'][] = $line;
                    continue 2;
                }

                if (!empty($states['refpurpose_lines'])) {
                    if (strpos($line, '</refpurpose>') !== false) {
                        // end of refpurpose block
                        $line = array_shift($states['refpurpose_lines']).implode(' ',$states['refpurpose_lines']).'</refpurpose>';
                        $states['refpurpose_lines'] = array();
                    } else {
                        $states['refpurpose_lines'][] = trim($line);
                        continue 2;
                    }
                }

                
                
                // WS on methodsynopsis (flush even horizontally with the refname)

                if (preg_match('#^([ ]+)<methodsynopsis#', $line, $match)) {
                    
                    // check the indenting on the line before
                    if(preg_match('#^([ ]+)<#', $lines[$key-1], $matchBefore)) {
                    
                        $indent       = strlen($match[1]);
                        $indentBefore = strlen($matchBefore[1]) - $states['front_spaces'];
                        
                        if ($indent > $indentBefore) {
                            $states['metsys_indent_diff'] = $indent - $indentBefore;
                        }
                    }
                    
                } 
                
                if ($states['metsys_indent_diff']) {
                    $line = substr($line, $states['metsys_indent_diff']);
                }
                
                if (strpos($line, '</methodsynopsis') !== false) {
                    $states['metsys_indent_diff'] = 0;
                }
                

                // method param should be on one line
                
                if (preg_match('#<methodparam#', $line) && strpos($line, '</methodparam') === false) {
                    $line = rtrim($line);
                    $states['methodparam_lines'][] = $line;
                    continue 2;
                }

                if (!empty($states['methodparam_lines'])) {
                    if (strpos($line, '</methodparam') !== false) {
                        // end of methodparam block
                        $line = array_shift($states['methodparam_lines']).implode(' ',$states['methodparam_lines']).trim($line);
                        $states['methodparam_lines'] = array();
                    } else {
                        $states['methodparam_lines'][] = trim($line);
                        continue 2;
                    }
                }

            } while(false);
            
            $tmp .= rtrim($line) . "\n";
    }
    
    if (false !== strpos($tmp, '<refsect1 role="')) {
            $log['newstyle'][] = $file;
            continue;
    } elseif (strlen($tmp) < 40) {
            $log['error'][] = "ERROR: File ($file) is empty";
            continue;
    } elseif (sha1($tmp) !== $hash) {
            $fp = fopen($fullpath_file, 'w');
            if ($fp) {
                    if (fwrite($fp, $tmp)) {
                            fclose($fp);
                            $log['rewritten'][] = $file;
                    }
            }
    }
}

echo count($log['rewritten'])." file(s) have been affected.\n";
if (!empty($log['error'])) {
    echo count($log['error'])." error(s) occured:\n";
    foreach($log['error'] as $error) {
        echo " $error\n";
    }
}
?>
