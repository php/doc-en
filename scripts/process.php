#!/usr/bin/php -q
<?php if ($argc < 2 || $argc > 3) { ?>
Process the manual to do some replacements.

  Usage:
  <?=$argv[0]?> <apply-script> [<startdir>]

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
    if ($new === FALSE) { echo "NO MODIFICATION: $file not modified"; }
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
        if ($file{0} != '.' && is_dir($ffile))
            $entries = array_merge($entries,all_xml_files($ffile));
    }
    closedir($handle);

    return $entries;
}
    

