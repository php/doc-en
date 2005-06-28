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

/** converts a tag like php_5_0_0 into a version like 5.0.0 */
function tag2version($tag) {
    $s = strtr(substr($tag, 4), '_', '.');

    return substr($s, 2) == 'cvs' ? $s{0} . '-cvs' : $s;
}


/** checks if an ini setting has changed its value in PHP 5  */
function check_php4($array) {

    foreach($array as $key => $val) {
        if (substr($key, 0, 5) != 'php_4') {
            continue;
        }

        if ($val) {
            if (isset($old)) {
                if ($val != $old) {
                    return '';
                }
            } else {
                $old = $val;
            }
        }
    }

    if (isset($old) && $old != $array['php_5_0_0'] && $array['php_5_0_0']) {
        return "$old in PHP 4.";
    }
}


/** return when the option become available */
function available_since($array) {

    if ($array['php_4_0_0']) {
        return '';
    }

    foreach($array as $key => $val) {
        if ($val) {
            return 'Available since PHP ' . tag2version($key) . '.';
        }
    }

}


/** check for changes between versions */
function last_version($array) {

    $php4 = check_php4($array);
    $str  = '';

    foreach($array as $key => $val) {
        if ($php4 && substr($key, 0, 5) == 'php_4') {
            continue;
        }

        if ($val) {
            if (isset($old)) {
                if ($val != $old) {
                    if ($old_tag == 'php_4_cvs') {
                        $str .= " $old in PHP &lt; 5.";
                    } else {
                        $str .= " $old in PHP &lt;= " . tag2version($old_tag) . '.';
                    }
                }
            }

            $old     = $val;
            $old_tag = $key;
        }
    }

    return $php4 . $str;
}


/** generate the changelog column */
function generate_changelog($array) {

    array_shift($array);
    return trim(last_version($array) . ' ' . available_since($array));
}



if (!$idx = sqlite_open('ini_changelog.sqlite', 0666, $error)) {
    die("Couldn't open the DB: $error");
}

$q = sqlite_unbuffered_query($idx, 'SELECT * FROM changelog');

/* This hack is needed because sqlite 2 sort case-sensitive */
while($row = sqlite_fetch_array($q, SQLITE_ASSOC)) {
    uksort($row, 'strnatcmp');
    $info[$row['name']] = $row;
}

uksort($info, 'strnatcasecmp');

foreach ($info as $row) {
    $changelog[$row['name']] = generate_changelog($row);
}

if (!isset($included)) {
    foreach ($changelog as $key => $val) {
        echo "$key : $val\n";
    }
}

sqlite_close($idx);

?>
