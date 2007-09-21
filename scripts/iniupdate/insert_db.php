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

require_once './ini_search_lib.php';
require_once './cvs-versions.php';

function insert_in_db($tag) {
    global $array, $idx;

    $sql = '';

    foreach ($array as $key => $data) {

        if (sqlite_single_query($idx, "SELECT name FROM changelog WHERE name='$key'")) {
            $sql .= "UPDATE changelog SET $tag='{$data[1]}' WHERE name='$key';";
        } else {
            $sql .= "INSERT INTO changelog (name, $tag) VALUES ('$key', '{$data[1]}');";
        }

    }

    if ($sql) sqlite_query($idx, $sql);
}



$db_open = isset($idx) ? true : false;

if (!$db_open && !$idx = sqlite_open('ini_changelog.sqlite', 0666, $error)) {
    die("Couldn't create the DB: $error");
}

// process PHP sources
foreach ($tags as $tag) {
    $array = array();
    recurse("./sources/$tag");
    insert_in_db($tag);

    echo "$tag\n";
}

// process PECL sources
foreach (get_pecl_releases_local() as $release) {

    preg_match('/^(.+)-(\d+\.\d+(?:\.\d+)?)$/S', $release, $m);
    $pkg     = $m[1];
    $version = $m[2];

    // if it has an entry already, just skip it
    if (sqlite_single_query($idx, "SELECT COUNT(*) FROM pecl_changelog WHERE package='".sqlite_escape_string($pkg)."' AND version='$version'") > 1) {
        continue;
    }

    $array = array();
    recurse("./sources/$release");

    $sql = '';

    foreach ($array as $key => $data) {
        $sql .= "INSERT INTO pecl_changelog (package, version, name, value) VALUES ('".sqlite_escape_string($pkg)."', '$version', '$key', '$data[1]');";
    }

    if ($sql) sqlite_query($idx, $sql);

    echo "$release\n";
}


if (!$db_open) {
    sqlite_close($idx);
    unset($idx);
}

?>
