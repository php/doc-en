<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
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
  | Authors:    Ken Tossell <kennyt@php.net>                             |
  +----------------------------------------------------------------------+

 $Id$
*/

/*
 * Usage:
 * $ php notes_extract.php source.mbox > notes.txt
 */

define('NL', "\n");

if (!isset($argv[1]) || $argv[1] == '-h' || $argv[1] == '--help') {
 echo 'USAGE: ', $argv[0], ' source.mbox > notes.txt', NL;
 exit(1);
}

if (!file_exists($argv[1])) {
 echo 'Error: Could not open file.', NL;
 exit(2);
}

define('SOURCE', $argv[1]);

$pairs = shell_exec(
 'gawk \'BEGIN { RS = "\n\n" } /Date: ([^\n]+).*Subject: \[PHP-NOTES]/ { print $0 }\' '.SOURCE.' | gawk \'/^Date:/ { print $0 } /^Subject:/ { print $0 }\''
);

$pairs = explode(NL, $pairs);

//echo 'Processing ', count($pairs) / 2, ' notes...', NL;

$last = count($pairs);

for ($i = 0; $i < $last; ++$i) {
 if (substr($pairs[$i], 0, 5) != 'Date:') {
  continue;
 }

 if (substr($pairs[$i + 1], 0, 8) != 'Subject:') {
  continue;
 }

 echo strtotime(substr($pairs[$i], 6)), ' ', substr($pairs[$i + 1], 9), NL;
}
