<?php

/* this script will delete all files that can be modified by
   the ini updater. It's very usefull when debugging! */

$phpdoc_dir  = '../..';

$ini_files   = glob("$phpdoc_dir/en/reference/*/ini.xml");
$ini_files[] = "$phpdoc_dir/en/features/safe-mode.xml";
$ini_files[] = "$phpdoc_dir/en/appendices/ini.xml";

foreach ($ini_files as $file) {
    unlink($file);
    echo "Deleted $file\n";
}

echo "fetch files from CVS again...\n";
system('cvs up ' . implode(' ', $ini_files));
?>
