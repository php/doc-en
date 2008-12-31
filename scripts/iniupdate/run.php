<?php
/*
  +----------------------------------------------------------------------+
  | ini doc settings updater                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2009 The PHP Group                                |
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

@mkdir('sources');

array_shift($argv);

foreach ($argv as $arg) {
    if ($arg === '-h' || $arg === '--help') {
        echo <<< HELP
possible options:
--skip-download		Do not download or update anything


HELP;
        exit;

    } elseif ($arg === '--skip-download') {
        $skip_download = true;
    } else {
        die("option not recognized: '$arg'\n");
    }

}


if (empty($skip_download)) {
    require_once './update-all.php';
}

if (is_file('ini_changelog.sqlite')) {
    require_once './update_db.php';
} else {
    require_once './make_db.php';
    require_once './insert_db.php';
}

require_once './ini-update.php';
