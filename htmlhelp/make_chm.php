<?php

/*
   This is the main file of the Windows Compiled HTML Help
   Manual Generator of the PHP Documentation project.
   
   Written by Gabor Hojtsy <goba@php.net>
   Credits go to people worked on previous versions:
   Derick Rethans, Jirka Kosek, Thomas Schoefbeck and
   Harald Radi
*/

// Measure needed time
$start_time = time();

// This script can take much time to run
set_time_limit(0);

// Include variables used by the build process
// Test for local_vars, as they are not there by default
if (!@file_exists("local_vars.php")) {
    die("ERROR: local_vars.php is needed, but not there");
}
require_once "local_vars.php";

// Possible languages for manual generation
$LANGUAGES = array(
    "cs", "de", "en", "es", "fr", "hu", "it", "ja", "kr", "nl", "pt_BR"
);

echo "
____________________________________________________________
Generate Microsoft HTML Help document from the
output of XSL generated HTML files of the PHP
documentation.

See \"local_vars.php.src\" for local parameter
adjustment, see README.txt and the phpdoc howto for
more information.
____________________________________________________________
";
  
// Process parameters
echo "\n| Processing local parameters...\n";

if (!@is_dir($HTML_SRC)) {
    die("ERROR: HTML path not valid");
}
if (!@file_exists($HELP_WORKSHOP)) {
    die("ERROR: HTML Workshop path not valid");
}

if (!@is_dir($RELEASE_DIR)) {
    mkdir($RELEASE_DIR, 0700);
    echo "> $RELEASE_DIR directory created...\n";
} elseif ($START_CLEANUP) {
    echo "| Cleaning up $RELEASE_DIR directory...\n";
    passthru("rmdir /S /Q $RELEASE_DIR");
    mkdir($RELEASE_DIR, 0700);
}

if (!@is_dir($HTML_TARGET)) {
    mkdir($HTML_TARGET, 0700);
    echo "> $HTML_TARGET directory created...\n";
} elseif ($START_CLEANUP) {
    echo "| Cleaning up $HTML_TARGET directory...\n";
    passthru("rmdir /S /Q $HTML_TARGET");
    mkdir($HTML_TARGET, 0700);
}

if ($USE_NOTES) {
    
    if (!@is_dir($NOTES_SRC)) {
        mkdir($NOTES_SRC, 0700);
        echo "> $NOTES_SRC directory created...\n";
    } elseif ($START_CLEANUP) {
        echo "| Cleaning up $NOTES_SRC directory...\n";
        passthru("rmdir /S /Q $NOTES_SRC");
        mkdir($NOTES_SRC, 0700);
    }
    
    if (!@is_dir($NOTES_TARGET)) {
        mkdir($NOTES_TARGET, 0700);
        echo "> $NOTES_TARGET directory created...\n";
    } elseif ($START_CLEANUP) {
        echo "| Cleaning up $NOTES_TARGET directory...\n";
        passthru("rmdir /S /Q $NOTES_TARGET");
        mkdir($NOTES_TARGET, 0700);
    }

    echo "| php_manual_notes.chm target choosen...\n";
    if ($LANGUAGE != 'en') {
        echo "| Warning: it is not safe to generate notes CHM for non-english HTML files\n";
    }
}

// Test for mirror information file
if (!file_exists("mirrors.inc")) {
    die("ERROR: Mirror information file (mirrors.inc) not found");
}

// Generate files and compile
echo "| Parameters processed...
";

echo "
> Now the HTML files are being filtered
  to make output as perfect as possible.
  Please be patient, as it can take some time...

";
$counter = 0;
require_once "filter_files.php";
echo "> $counter files are converted in previous step.
";

if ($USE_NOTES) {

    $WITH_NOTES = $WITHOUT_NOTES = $NOTE_NUM = 0;
    echo "
> Trying to split user notes 'all' file to separate
  directories and files (to make the next step
  fast and less resource intensive)...
";
    require_once "split_notes.php";
    echo "
> Now the user notes project file and HTML files
  are being created. Please be patient, as it
  can take as much time as the previous step...
  
";
    require_once "user_notes.php";
    echo "> $WITH_NOTES files created with notes
  $WITHOUT_NOTES files found without notes
  Total number of notes: $NOTE_NUM
";

}

echo "
> Now creating mirrors.ini file from current mirrors.inc...
";
require_once "mirrors_ini.php";

echo "
> Now starting HTML Help Workshop...
____________________________________________________________

";

// Run HTML Help Workshop, and move files to release directory
passthru($HELP_WORKSHOP . " $HTML_TARGET\\php_manual_$LANGUAGE.hhp");
rename("$HTML_TARGET/php_manual_$LANGUAGE.chm", "$RELEASE_DIR/php_manual_$LANGUAGE.chm");

if ($USE_NOTES) {
    passthru($HELP_WORKSHOP . " $NOTES_TARGET\\php_manual_notes.hhp");
    rename("$NOTES_TARGET/php_manual_notes.chm", "$RELEASE_DIR/php_manual_notes.chm");
}

// Copy PHP manual preferences, qucikref and skins files to release directory
// $RELEASE_DIR = str_replace("/", "\\", $RELEASE_DIR):
exec("copy suppfiles\\prefs $RELEASE_DIR /Y");
exec("copy suppfiles\\quickref $RELEASE_DIR /Y");
exec("xcopy suppfiles\\skins $RELEASE_DIR\\skins /S /I /Q /EXCLUDE:exclude.txt");

// Delete unused files
if ($END_CLEANUP) {
    echo "> Removing every file except the release files...\n";
    passthru("rmdir /S /Q $HTML_SRC");
    passthru("rmdir /S /Q $HTML_TARGET");
    passthru("rmdir /S /Q $NOTES_SRC");
    passthru("rmdir /S /Q $NOTES_TARGET");
}

// Measure time
$alltime = time() - $start_time;
echo "____________________________________________________________\n";
echo "Conversion done in " . ((int) ($alltime / 60)) . " minutes ";
echo "and " . $alltime % 60 . " seconds.\n";
echo "You can find the generated file[s] in $RELEASE_DIR directory\n";
echo "____________________________________________________________\n";

?>