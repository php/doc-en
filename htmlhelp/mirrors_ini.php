<?php

/* 
   This file is part of the Windows Compiled HTML Help
   Manual Generator of the PHP Documentation project.
   
   This script generates mirrors.ini from mirrors.inc ( can be
   downloaded from http://MIRROR.php.net/include/mirrors.inc )
*/

// Load the mirrors file in
include_once "mirrors.inc";

// Get mirror site addresses
$mirrors = array_keys($MIRRORS);

// Write out mirror information
$fp = fopen($RELEASE_DIR . "/mirrors.ini", "w");

fwrite($fp,
"# =================================================
# PHP Manual CHM version mirror list configuration
# =================================================

# Possible mirrors for online functions (only those
# mirrors are listed, which were active when this
# list was generated)

[mirrors]
");

// Write out all active mirror sites
foreach ($MIRRORS as $mirror => $mirrrorinfo) {
    if ($mirrorinfo[7] == MIRROR_OK) {
	    fwrite($fp, "mirror = \"$mirror\"\n");
    }
}

fclose($fp);

?>