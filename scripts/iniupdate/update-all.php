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


/** find a dir inside the current pwd */
function find_a_dir()
{
    foreach (scandir('.') as $f) {
        if ($f !== '.' && $f !== '..' && is_dir($f)) return $f;
    }

    return false;
}


/** fetch a tag sources */
function download_sources($url, $dir, $filename, $finaldir)
{
    if (is_dir($finaldir)) {
        echo "already there\n";
        return;
    }

    @mkdir('tmp');
    chdir('tmp');

    if (!@copy($url, $filename)) {
        echo "\033[1;31mFAILED\033[0m\n";
        chdir('..');
        `rm -fr tmp`;
        return;
    }

    $filename = escapeshellarg($filename);

    `tar xfz $filename 2>&1 | grep -v "A lone zero block at"`; // silence some warnings from tar

    // this is needed because PECL packages don't have a naming standard for directories
    $dir = find_a_dir();
    if (!$dir) {
        die("directory not found for the following file: $filename\n");
    }

    $dir      = escapeshellarg($dir);
    $finaldir = escapeshellarg($finaldir);

    if ($finaldir !== $dir) {
        $cmds[] = "mv $dir $finaldir";
    }

    $cmds[] = 'find ' .$finaldir. ' -type f -and -not -regex ".*\.\([chly]\|cpp\|cc\)" -and -not -name "*.ec" -and -not -name "*.lex" -delete';
    $cmds[] = 'while ( find ' .$finaldir. ' -depth -mindepth 1 -type d -and -empty | xargs rm -r 2>/dev/null ) ; do true ; done';
    $cmds[] = "mv $finaldir ..";

    foreach ($cmds as $cmd) {
        exec($cmd);
    }

    chdir('..');
    `rm -fr tmp`;

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
    $dir = strtolower($tag);

    // if the dir already exists, perform an update rather than a checkout
    if (is_dir($dir)) {
        `cd $dir && cvs -q up -dP`;

         // zend dirs require special handling because cvs is damn stupid..
         if (is_dir("$dir/Zend")) `cd $dir/Zend && cvs -q up -dP`;
         if (is_dir("$dir/ZendEngine2")) `cd $dir/ZendEngine2 && cvs -q up -dP`;
    } else {
        `cvs -q -d :pserver:cvsread@cvs.php.net:/repository co -d $dir -r $branch -P php-src`;
    }

    echo "done\n";
}

update_pecl_sources();

chdir('..');
