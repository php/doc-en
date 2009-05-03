<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
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
  | Author:     Kalle Sommer Nielsen <kalle@php.net>                     |
  +----------------------------------------------------------------------+
  
  $Id$
*/

/* A damn simple "cat" program to fit the needs from the makefile for Windows */

if ($argc < 2) {
    die("Atleast one argument must be passed\n");
}

array_shift($argv);

foreach($argv as $file) {
    echo file_get_contents($file);
}

?>