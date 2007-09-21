<?php
/*
  +----------------------------------------------------------------------+
  | ini doc settings updater                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
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


// if run alone, it means debug mode and thus no slow network access
if (empty($included)) {
    $skip_download = true;
}

require_once './cvs-versions.php';


/** converts a tag like php_5_0_0 into a version like 5.0.0 */
function tag2version($tag)
{
    global $cvs_versions;

    if (isset($cvs_versions[$tag]))
        return $cvs_versions[$tag];

    return strtr(substr($tag, 4), '_', '.');
}


/** returns TRUE if the opt is/was present in PHP (i.e. it is not in PECL only) */
function in_php($array)
{
   return (substr_compare(key($array), 'PHP_', 0, 4, true) == 0);
}


/** returns the package name of the given array/string */
function pkg_name($array)
{
    $input = is_array($array) ? key($array) : $array;
    preg_match('/(.+)-(\d+\.\d+(?:\.\d+)?)/S', $input, $m);
    return $m[1];
}


/** fetch the local PECL releases of the given pkg name */
function get_local_pecl_releases($array)
{
    static $cache = array();

    $pecl_releases = get_pecl_releases_local();

    $pkg = pkg_name($array);

    if (isset($cache[$pkg])) return $cache[$pkg];

    $pkg_strlen = strlen($pkg);

    foreach ($pecl_releases as $release) {
        if (substr_compare($pkg, $release, 0, $pkg_strlen) == 0) {
            $cache[$pkg][] = $release;
        }
    }

    return $cache[$pkg];
}


/** return when the option become available */
function available_since($array)
{
    $ver = null;

    if (in_php($array)) {
        if (!current($array)) {
            foreach ($array as $tag => $val) {
                if ($val) {
                    $ver = 'PHP '. tag2version($tag);
                    break;
                }
            }
        }

    // PECL only
    } else {
        $releases = get_local_pecl_releases($array);

        if (key($array) !== current($releases)) {
            foreach ($releases as $rel) {
                if ($rel === key($array)) {
                    $ver = pkg_name($array) .' '. tag2version($rel);
                    break;
                }
            }
        }
    }

    return $ver ? "Available since $ver." : '';
}


/** check for changes between versions */
function last_version($array)
{
    // TODO again
}


/** return when the option was removed */
function removed_in($array)
{
    $ver = null;

    if (in_php($array)) {
        $on = false;

        foreach ($array as $tag => $val) {
            if ($val) {
                $on = true;

            } elseif ($on) {
                $ver = 'PHP '. tag2version($tag);
                break;
            }
        }

    // PECL only
    } else {
        $releases = get_local_pecl_releases($array);

        $on = false;
        end($array);

        if (end($releases) !== key($array)) {
            foreach ($releases as $release) {
                if ($release === key($array)) {
                    $on = true;

                } else if ($on) {
                    $ver = pkg_name($array) .' '. tag2version($release);
                    break;
                }
            }
        }
    }


    return $ver ? "Removed in $ver." : '';
}


/** generate the changelog column */
function generate_changelog($array)
{
    $data = array(
        last_version($array),
        available_since($array),
        removed_in($array),
    );


    $str = '';

    foreach ($data as $s) {
        if ($s) {
            $str .= ($str ? ' ' : '') . $s;
        }
    }

    return $str;
}



if (!$idx = sqlite_open('ini_changelog.sqlite', 0666, $error)) {
    die("Couldn't open the DB: $error");
}

$q = sqlite_unbuffered_query($idx, 'SELECT * FROM changelog');

/* This hack is needed because sqlite 2 sort case-sensitive */
while ($row = sqlite_fetch_array($q, SQLITE_ASSOC)) {
    $name = $row['name'];
    unset($row['name']);
    uksort($row, 'strnatcasecmp');
    $info[$name] = $row;
}


$q = sqlite_unbuffered_query($idx, 'SELECT * FROM pecl_changelog');

while ($row = sqlite_fetch_array($q, SQLITE_ASSOC)) {
    $info[$row['name']][$row['package'].'-'.$row['version']] = $row['value'];
    uksort($info[$row['name']], 'strnatcasecmp');
}


uksort($info, 'strnatcasecmp');

foreach ($info as $name => $row) {
    $changelog[$name] = generate_changelog($row);
}

// if in debug mode
if (empty($included)) {
    foreach ($changelog as $key => $val) {
        echo "$key : $val\n";
    }
}

sqlite_close($idx);
unset($idx);

?>
