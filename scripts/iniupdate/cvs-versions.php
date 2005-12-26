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

// fetch all version tags
$tags = array();
foreach (glob('*.tags') as $file) {
    $tmp  = array_map('rtrim', file($file));
    $last_versions[] = substr(end($tmp), 4); // this is the last released version from a major version
    $tags = array_merge($tags, $tmp);
}


// fetch cvs versions
$file = file_get_contents('./update-all');
preg_match_all('/PHP_(\d)_CVS=(\w+)/', $file, $data, PREG_SET_ORDER);

$cvs_versions = array();
foreach ($data as $v) {
	if ($v[2] == 'HEAD') {
		$version = "PHP $v[1].0.0";
	} else {
		$version = 'PHP ' . make_cvs_version(substr($v[2], 4));
	}
	$cvs_versions["php_$v[1]_cvs"] = $version;
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
