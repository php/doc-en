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
  | Authors:    Nuno Lopes <nlopess@php.net>                             |
  +----------------------------------------------------------------------+

 $Id$
*/

/*
 * This script searches for orphan notes.
 * You need a phpweb checkout with, at least,
 * manual/en and backend/notes folders
 */


/* Configuration Options */

$manual_dir = 'manual/en';
$notes_dir = 'backend/notes';

/******* END of configurations *****/


/* Collect manual IDs */
function recurse_manual($dir) {
  global $array;

  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {

      if($file != '.' && $file != '..') {
        $path = $dir.'/'.$file;

        if(is_dir($path)) {
          recurse_manual($path);
        } else {
          $array[substr(md5(substr($path, $GLOBALS['len'], -4)), 0, 16)] = 1;
        }

      }
    }
  closedir($dh);
  }
}


/* Search for bogus notes IDs */
function recurse_notes($dir) {
  global $array, $files, $notes;

  if ($dh = opendir($dir)) {
    while (($file = readdir($dh)) !== false) {

      if($file != '.' && $file != '..' && substr($file, -4) != '.bz2') {
        $path = $dir.'/'.$file;

        if(is_dir($path)) {
          recurse_notes($path);
        } else {
          if(!isset($array[$file]) && $file != 'last-updated' && $file != 'sections') {
            echo "file: $path\n";

            $fp = fopen($path, "r");
            while (!feof($fp)) {
              $line = chop(fgets($fp, 12288));
              if ($line == "") { continue; }

              list($id, $sect, , , , ) = explode("|", $line);
              ++$notes;

              if (!isset($done)) {
                $done = 1;
                echo "old ID: $sect\nNotes IDs: $id";

              } else {
                echo ", $id";
              }
            }
            echo "\n\n";
            ++$files;
          }

        unset($done);
        }
      }
    }
  closedir($dh);
  }
}

$array = array();
$len = strlen("$manual_dir/");
$files = $notes = 0;

recurse_manual($manual_dir);
recurse_notes($notes_dir);


echo "\nTotal files: $files\nTotal notes: $notes";
?>
