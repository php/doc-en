#!/usr/bin/php -q
<?php 
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Jeroen van Wolffelaar <jeroen@php.net>                   |
  +----------------------------------------------------------------------+
*/

set_time_limit(0);
ob_implicit_flush();

if ($argc < 2 || $argc > 3) { ?>
Process the manual to do some replacements.

  Usage:
  <?php echo $argv[0]; ?> <apply-script> [<startdir>]

  <apply-script> must contain the function apply($input),
  which recieves a whole xml-file, and should return
  the new file, or false if no modification needed.
  Apply scripts reside in the apply folder below this
  script. You only need to give the file name.

  With <startdir> you can specify in which dir
  to start looking recursively for xml files.

  Written by jeroen@php.net

<?php
    exit;
}

echo "Starting with manual-process\n";
echo "Including $argv[1]...";
include("apply/$argv[1]");
echo " done\n";
if (!function_exists('apply')) {
?>

### FATAL ERROR ###

In <?=$argv[1]?> you should define a function:
  string apply(string $string)

<?php
    exit;
}

$startdir = isset($argv[2]) ? $argv[2] : '.';

echo "Constructing list of all xml-files (may take a while)...";
$files = all_xml_files($startdir);
echo " done (".count($files)." xml files found)\n";

foreach ($files as $file) {

    echo "[Processing $file]\n";
    $fp = fopen($file,'r');
    $old = fread($fp,filesize($file));
    fclose($fp);

    if (!$old) {
        echo "WARNING: problem reading $file, skipping\n";
        continue;
    }

    $new = apply($old);
    if ($new === FALSE) { echo "NO MODIFICATION: $file not modified\n"; }
    else {
        $fp = fopen($file,'w');
        $res = fwrite($fp,$new);
        fclose($fp);
        if (!$res) {
            echo "WARNING: problem writing $file, file might be damaged\n";
            continue;
        }
    }
}

    
/* Utility functions: */

function all_xml_files($startdir)
{
    $startdir = ereg_replace('/+$','',$startdir);
    //echo "\$startdir = $startdir\n";
    $entries = array();

    $handle=opendir($startdir);
    while ($file = readdir($handle)) {

        $ffile = "$startdir/$file"; // full file(path)
        //echo "$file\n";
        if (ereg('\.xml$',$file))
            $entries[] = $ffile;
        if ($file[0] != '.' && is_dir($ffile))
            $entries = array_merge($entries,all_xml_files($ffile));
    }
    closedir($handle);

    return $entries;
}
    

