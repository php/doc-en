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


/** fetch the PHP release tags */
function get_php_release_tags()
{
    static $tags = null;

    if ($tags) return $tags;

    chdir('sources');

    if (empty($GLOBALS['skip_download'])) {
        `cvs -q -d :pserver:cvsread@cvs.php.net:/repository co php-src > /dev/null`;
    }

    chdir('php-src');

    $log = explode("\n", `cvs log ChangeLog`);
    chdir('../..');

    do {
        $l = array_shift($log);
        if ($l == 'symbolic names:') {
            break;
        }
    } while (1);

    $tags = array();
    foreach ($log as $l) {
        if (substr($l, 0, 1) != "\t") {
            break;
        }
        list($tag) = explode(': ', trim($l));
        if (preg_match('/^PHP_[456]_[0-9]+_[0-9]+$/i', $tag)) {
            $tags[] = $tag;
        }
    }

    natcasesort($tags);
    $tags = array_map('strtoupper', $tags);
    $tags = array_unique($tags);

    return $tags;
}


// fetch all version tags
$tags    = get_php_release_tags();
$lasttag = 'PHP_4_0_0';

foreach (array_merge($tags, array('php_head')) as $tag) {
    if ($tag[4] === $lasttag[4]) {
        $lasttag = $tag;
        continue;
    }

    $last_versions[] = substr($lasttag, 4); // this is the last released version from a major version
    $lasttag = $tag;
}


// fetch cvs versions
$file = file_get_contents('./cvs-versions');
preg_match_all('/PHP_(\d+)_CVS=(\w+)/', $file, $data, PREG_SET_ORDER);

$cvs_versions = $cvs_branches = array();
foreach ($data as $v) {
    if ($v[2] == 'HEAD') {
        $version = "$v[1].0.0";
    } else {
        $version = make_cvs_version(substr($v[2], 4));
    }
    $cvs_versions["php_$v[1]_cvs"] = $version;
    $cvs_branches["php_$v[1]_cvs"] = $v[2];
}

$tags = array_merge(array_keys($cvs_versions), $tags);

// the file was called directly: DEBUG mode
if (basename(__FILE__) == $_SERVER['PHP_SELF']) {
    print_r($cvs_versions);
    print_r($last_versions);
    print_r($tags);
}


// guess the cvs version number by checking last released versions
function make_cvs_version($tag) {
    global $last_versions;

    foreach ($last_versions as $ver) {
        if (strpos($ver, $tag) === 0) { //found it
            $parts = explode('_', $ver);
            ++$parts[2]; //increment minor version (5.5.1 -> 5.5.2)
            return implode('.', $parts);
        }
    }

    // new branch (5.5.0)
    return "$tag[0].$tag[2].0";
}

unset($file, $data, $last_versions);
?>
