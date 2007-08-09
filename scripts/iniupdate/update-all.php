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

/** fetch the PHP release tags */
function get_php_release_tags()
{
    chdir('php-src');

    $log = explode("\n", `cvs log ChangeLog`);

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
        list($tag,) = explode(': ', trim($l));
        if (preg_match('/^PHP_[456]_[0-9]+_[0-9]+$/i', $tag)) {
            $tags[] = $tag;
        }
    }

    chdir('..');

    return array_reverse($tags);
}

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

    copy("http://museum.php.net/php$majorversion/$filename", $filename);

    $cmds[] = "tar xfz $filename";
    $cmds[] = "mv $dir $tag";
    $cmds[] = "rm $filename";
    $cmds[] = 'find ' .escapeshellarg($tag). ' -type f -and -not -name "*.[chly]" -and -not -name "*.ec" -and -not -name "*.lex" | xargs rm -f';
    $cmds[] = 'while ( find ' .escapeshellarg($tag). ' -depth -type d -and -empty | xargs rm -r 2>/dev/null ) ; do true ; done';

    foreach ($cmds as $cmd) {
        exec($cmd);
    }

    echo "done\n";
}


// update HEAD
echo "updating cvs HEAD... ";
chdir('sources');
$cmd = 'cvs -q -d :pserver:cvsread@cvs.php.net:/repository co php-src > /dev/null';
//exec($cmd);
echo "done\n";

foreach (get_php_release_tags() as $tag) {
    $tag = strtoupper($tag);
    echo "Getting tag: $tag... ";
    checkout_tag($tag);
}

