#!/usr/bin/php -q
<?php

if ($argc > 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

Check entities in entities/global.ent (HTTP and FTP schemes)

  Usage:
  <?=$argv[0]?>

  This script checks FTP and HTTP URLs listed
  in entities/global.ent. Grab the output, to put it in
  a text file.
  
  Written by Georg Richter <georg@php.net>
  Adapted to phpdoc by Gabor Hojtsy <goba@php.net>

<?php
  exit;
}

// CONFIG SECTION
$docdir = "../"; // Main directory of the PHP documentation (one dir up in cvs)

/*********************************************************************/
/* Nothing to modify below this line                                 */
/*********************************************************************/

// Like a good wine, this script needs some time
set_time_limit(0);

// Schemes we had to check
$schemes = array("http", "ftp");

// Start this script only from the scripts dir
$filename = "../entities/global.ent";

// Read in the file, or die
$file_array = file ($filename);
if (!$file_array) { die ("Cannot open entity file ($filename)."); }

// Put the whole file in a string
$file_string = preg_replace("/[\r\n]/", "", join ("", $file_array));

echo "ENTITY CHECK

=========================================================
In the table below you can find the validity check
errors of entites in $filename. Use this list to correct
errors in $filename.
=========================================================

";

// Find entity names and URLs
$schemes_preg = "(" . join("|", $schemes) . ")";
preg_match_all("/<!ENTITY\s+(\S+)\s+([\"'])(({$schemes_preg})[^\\2]+)\\2\s*>/U",
    $file_string, $entities_found);

// These are the useful parts
$entity_names = $entities_found[1];
$entity_urls  = $entities_found[3];

// Walk through entities found
foreach ($entity_urls as $num => $entity_url) {

    // Get the parts of the URL
    $url = parse_url($entity_url);
    $entity = $entity_names[$num];

    // Try to find host
    $ip = gethostbyname($url["host"]);
    if ($ip == $url["host"]) {
        errormsg ($entity, "unknown host: " . $url["host"]);
    // Host found, check path
    } else {

        // Depending on URL scheme
        switch ($url["scheme"]) {
    
            // Use URL fopen wrapper
            case "http":
                if ($fpurl = @fopen($entity_url, "r")) {
                    fclose ($fpurl);
                }
                else {
                    errormsg ($entity, "Could not open document: " . $entity_url);
                }
            break;
    
            // Use FTP functions
            case "ftp":
                if ($ftp = @ftp_connect($url["host"])) {
                    if (@ftp_login($ftp, "anonymous", "georg@php.net")) {
                        $flist = ftp_nlist($ftp, $url["path"]);
                        if (!count($flist)) {
                            errormsg($entity, "unknown path: " . $url["path"] . " for ftp host: " . $url['host']);
                        }
                    } else {
                        errormsg ($entity, "could not login as anonymous to FTP host: " . $url["host"]);
                        ftp_quit($ftp);
                    }
                } else {
                    errormsg ($entity, "could not connect to " . $url["host"]);
                }
            break;
        }
    }
}


/*********************************************************************/
/* Here starts the functions part                                    */
/*********************************************************************/
function errormsg ($entity, $desc)
{
    printf ("%30s: %s\n", $entity, $desc);
}

?>
