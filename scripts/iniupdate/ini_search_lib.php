<?php
/*
  +----------------------------------------------------------------------+
  | ini doc settings updater                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2005 The PHP Group                                |
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
*/

function recurse($dir) {
    global $array;

    if (!$dh = opendir($dir)) {
        die ("couldn't open the specified dir ($dir)");
    }

    while (($file = readdir($dh)) !== false) {

        if($file == '.' || $file == '..') {
            continue;
        }

        $path = $dir . '/' .$file;

        if(is_dir($path)) {
            recurse($path);
        } else {
            $file = file_get_contents($path);

            /* delete comments */
            $file = preg_replace('@(//.*$)|(/\*.*\*/)@SmsU', '', $file);

            /* The MAGIC Regexp :) */
            if(preg_match_all('/(?:PHP|ZEND)_INI_(?:ENTRY(?:_EX)?|BOOLEAN)\s*\(\s*"([^"]+)"\s*,((?:".*"|[^,])+)\s*,\s*([^,]+)/S', $file, $matches)) {

                $count = count($matches[0]);
                for($i=0;$i<$count;$i++) {

                    $default = htmlspecialchars(trim($matches[2][$i]), ENT_NOQUOTES);

                    $permissions = preg_replace(array('/\s+/', '/ZEND/'), array('', 'PHP'), $matches[3][$i]);
                    $permissions =  ($permissions == 'PHP_INI_PERDIR|PHP_INI_SYSTEM' || $permissions == 'PHP_INI_SYSTEM|PHP_INI_PERDIR') ? 'PHP_INI_PERDIR' : $permissions;

                    $array[] = array($matches[1][$i], $permissions);
                }
            } //end of the magic regex

        } //!is_dir()
    } //while() loop

    closedir($dh);
}
?>
