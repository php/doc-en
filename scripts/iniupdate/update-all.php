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

require_once './cvs-versions.php';
require_once './pecl.php';


/** find a dir in a case-insensitive way */
function try_dir_combinations($dir)
{
    $len = strlen($dir);
    $pattern = '';

    for ($i=0; $i<$len; ++$i) {
        if (ctype_alpha($dir[$i])) {
            $pattern .= '['.strtolower($dir[$i]).strtoupper($dir[$i]).']';
        } else {
            $pattern .= $dir[$i];
        }
    }

    $match = glob($pattern);

    return $match ? $match[0] : null;
}


/** fetch a tag sources */
function download_sources($url, $dir, $filename, $finaldir)
{
    if (is_dir(try_dir_combinations($finaldir))) {
        echo "already there\n";
        return;
    }

    if (!@copy($url, $filename)) {
        echo "\033[1;31mFAILED\033[0m\n";
        return;
    }

    $filename = escapeshellarg($filename);

    `tar xfz $filename 2>&1 | grep -v "A lone zero block at"`; // also skip some warnings from tar

    // this is needed because PECL packages differ
    $dir = try_dir_combinations($dir);
    if (!$dir) {
        die("directory not found for the following file: $filename\n");
    }

    $dir      = escapeshellarg($dir);
    $finaldir = escapeshellarg($finaldir);

    if ($finaldir != $dir) {
        $cmds[] = "mv $dir $finaldir";
    }

    $cmds[] = "rm $filename";
    $cmds[] = 'find ' .$finaldir. ' -type f -and -not -name "*.[chly]" -and -not -name "*.ec" -and -not -name "*.lex" | xargs rm -f';
    $cmds[] = 'while ( find ' .$finaldir. ' -depth -type d -and -empty | xargs rm -r 2>/dev/null ) ; do true ; done';

    foreach ($cmds as $cmd) {
        exec($cmd);
    }

    echo "\033[1;32mdone\033[0m\n";
}


/** fetch a tag sources */
function checkout_tag($tag)
{
    // $tag = PHP_x_x_x
    $majorversion = substr($tag, 4, 1);
    $dir          = 'php-'.strtr(substr($tag, 4), '_', '.');
    $filename     = "$dir.tar.gz";
    $url          = "http://museum.php.net/php$majorversion/$filename";

    download_sources($url, $dir, $filename, $tag);
}


chdir('sources');

foreach (get_php_release_tags() as $tag) {
    $tag = strtoupper($tag);
    echo "Getting tag: $tag... ";
    checkout_tag($tag);
}

foreach ($cvs_branches as $tag => $branch) {
    echo "Getting tag: $tag... ";
    $cmd = "cvs -q -d :pserver:cvsread@cvs.php.net:/repository co -d ".strtolower($tag)." -r $branch php-src";
    exec($cmd);
    echo "done\n";
}

update_pecl_sources();

chdir('..');
