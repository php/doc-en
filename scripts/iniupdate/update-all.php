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


/** fetch a tag sources */
function checkout_tag($tag)
{
    if (is_dir($tag)) {
        echo "already there\n";
        return;
    }

    // $tag = PHP_x_x_x
    $majorversion = substr($tag, 4, 1);
    $dir          = 'php-'.strtr(substr($tag, 4), '_', '.');
    $filename     = "$dir.tar.gz";

    if (!@copy("http://museum.php.net/php$majorversion/$filename", $filename)) {
        echo "\033[1;31mFAILED\033[0m\n";
        return;
    }

    $cmds[] = "tar xfz $filename";
    $cmds[] = "mv $dir $tag";
    $cmds[] = "rm $filename";
    $cmds[] = 'find ' .escapeshellarg($tag). ' -type f -and -not -name "*.[chly]" -and -not -name "*.ec" -and -not -name "*.lex" | xargs rm -f';
    $cmds[] = 'while ( find ' .escapeshellarg($tag). ' -depth -type d -and -empty | xargs rm -r 2>/dev/null ) ; do true ; done';

    foreach ($cmds as $cmd) {
        exec($cmd);
    }

    echo "\033[1;32mdone\033[0m\n";
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

chdir('..');
