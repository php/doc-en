<?php
/*
  +----------------------------------------------------------------------+
  | ini doc settings updater                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2009 The PHP Group                                |
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

// limit the macro expansion to avoid possible infinite loops
define('MAX_MACRO_EXPAND_DEPTH', 10);



function recurse($dirs, $search_macros = false) {
    global $array;

    $cfg_get = array();

    if (is_array($dirs)) {
        foreach($dirs as $dir)
            recurse_aux($dir, $search_macros, $cfg_get);
    } else {
        recurse_aux($dirs, $search_macros, $cfg_get);
    }

    /* insert only if the key doesn't exist, as will probably have
       more accurant data in $array than here */
    foreach($cfg_get as $entry) {
        if (!isset($array[$entry[0]]))
            $array[$entry[0]] = array($entry[1], 'PHP_INI_ALL');
    }

    uksort($array, 'strnatcasecmp');
}


// recurse through the dirs and do the 'dirty work'
function recurse_aux($dir, $search_macros, &$cfg_get) {
    global $array, $replace;

    if (is_file($dir)) {
        $files = array(basename($dir));
        $dir   = dirname($dir);
    } else {
        if (!is_file($dir) && !$files = @scandir($dir)) {
            echo "$dir - FAILED TO SCAN DIR\n";
            return;
        }
        unset($files[0], $files[1]); //remove the . and ..
    }

    foreach ($files as $file) {

        $path = $dir . '/' .$file;

        if (is_dir($path)) {
            recurse_aux($path, $search_macros, $cfg_get);
        } else {
            $file = file_get_contents($path);

            /* delete comments */
            $file = preg_replace('@(//.*$)|(/\*.*\*/)@SmsU', '', $file);

            /* The MAGIC Regexp :) */
            if (preg_match_all('/(?:PHP|ZEND)_INI_(?:ENTRY(?:_EX)?|BOOLEAN)\s*\(\s*"([^"]+)"\s*,((?:".*"|[^,])+)\s*,\s*([^,]+)/S', $file, $matches)) {

                $count = count($matches[0]);
                for ($i=0; $i<$count; ++$i) {

                    $default = htmlspecialchars(trim($matches[2][$i]), ENT_NOQUOTES);

                    $permissions = preg_replace(array('/\s+/', '/ZEND/'), array('', 'PHP'), $matches[3][$i]);
                    $permissions =  ($permissions == 'PHP_INI_PERDIR|PHP_INI_SYSTEM' || $permissions == 'PHP_INI_SYSTEM|PHP_INI_PERDIR') ? 'PHP_INI_PERDIR' : $permissions;

                    $array[$matches[1][$i]] = array($default, $permissions);
                }

            } //end of the magic regex


            // find the nasty cfg_get_*() stuff
            if (preg_match_all('/cfg_get_([^(]+)\s*\(\s*"([^"]+)",\s*&([^\s=]+)\s*\)/S', $file, $match, PREG_SET_ORDER)) {

                foreach ($match as $arr) {
                    preg_match('/(?:(FAILURE|SUCCESS)\s*==\s*)?'.preg_quote($arr[0]).'(?:\s*==\s*(FAILURE|SUCCESS))?(?:(?:[^=]|==){1,40}'.preg_quote($arr[3]).'\s*=\s*(.+);)?/', $file, $m);

                    // if the default value wasn't found default to SUCCESS
                    if (isset($m[1]) && ($m[1] === 'FAILURE' || $m[2] === 'FAILURE')) {
                        $cfg_get[] = array($arr[2], $arr[1] == 'string' ? $m[3] : '"'.$m[3].'"');

                    } else { //$m[1] == 'SUCCESS'
                        if ($arr[1] == 'string')
                            $cfg_get[] = array($arr[2], '""');
                        else
                            $cfg_get[] = array($arr[2], '"0"');
                    }
                } //foreach cfg_get_*()
            } //end of nasty cfg_get_*() regex


            /* search for C macros */
            if($search_macros && preg_match_all('/#\s*define\s+(\S{5,})[ \t]+(.+)/S', $file, $matches)) {
                $count = count($matches[0]);
                for($i=0;$i<$count;$i++) {
                    $replace[$matches[1][$i]] = rtrim($matches[2][$i]);
                }
            } // end of macros


        } //!is_dir()
    } //while() loop
}


/** expand macros on the given string */
function expand_macros($str)
{
    global $replace;

    $new = $str;
    $old = null;
    $i = 0;

    while ($new[0] !== '"' && $new !== $old && ++$i < MAX_MACRO_EXPAND_DEPTH) {
        $old = $new;
        $new = strtr($new,$replace);
    }

    return $new;
}
