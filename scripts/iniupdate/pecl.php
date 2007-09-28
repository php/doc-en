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
  | Authors: Nuno Lopes <nlopess@php.net>                                |
  +----------------------------------------------------------------------+
*/


/** returns an array with the PECL packages */
function get_pecl_packages()
{
    static $cache = null;
    if ($cache) return $cache;

    $packages = array();
    $XE = @new SimpleXMLElement('http://pecl.php.net/rest/p/packages.xml', NULL, true);

    foreach ($XE as $Element) {
        if ($Element->getName() == 'p') {
            $packages[] = (string) $Element;
        }
    }

    return $cache = $packages;
}


/** returns an array with the releases of the given PECL package */
function get_pecl_releases($package)
{
    try {
        $releases = array();
        $package  = strtolower($package);
        $url      = "http://pecl.php.net/rest/r/$package/allreleases.xml";

        // simplexml doesnt seem to be able to handle the 404 errors as I would like..
        if (@file_get_contents($url, 0, null, 0, 1) === false) return $releases;

        $XE = @new SimpleXMLElement($url, NULL, true);

        foreach ($XE as $Element) {
            if ($Element->getName() == 'r') {
                if (preg_match('/\d+\.\d+(?:\.\d+)?$/', (string) $Element->v)) {
                    $releases[] = (string) $Element->v;
                }
            }
        }

        natsort($releases);
        return $releases;

    } catch (Exception $e) {
        print_r($e);
        exit;
    }
}


/** download a PECL release (if needed) */
function grab_pecl_release($package, $release)
{
    $package  = strtolower($package);

    $url  = 'http://pecl.php.net/get/'. urlencode($package) . '-' . urlencode($release);
    $dir  = "$package-$release";
    $file = "$dir.tgz";

    download_sources($url, $dir, $file, $dir);
}


/** update PECL sources */
function update_pecl_sources()
{
    `cvs -q -d :pserver:cvsread@cvs.php.net:/repository co -P pecl > /dev/null`;

    foreach (get_pecl_packages() as $pkg) {
        $releases = get_pecl_releases($pkg);

        foreach ($releases as $ver) {
            echo "fetching PECL package: $pkg-$ver... ";
            grab_pecl_release($pkg, $ver);
        }
    }
}
