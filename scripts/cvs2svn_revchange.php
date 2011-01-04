#!/usr/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 2009-2011 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Nilgün Belma Bugüner <nilgun@php.net>                           |
  +----------------------------------------------------------------------+

  $Id$
*/
if ($argc != 2) {
?>

Check the revision of translated files against the actual english xml files,
and change revision of translated files from cvs to svn

  Usage:
  <?php echo $argv[0]; ?> <language-code>

  <language-code> must be a valid language code used
  in the repository

  Read more about Revision comments and related
  functionality in the PHP Documentation Howto:
    http://php.net/dochowto

<?php
  exit;
}

// Long runtime
set_time_limit(0);


// Initializing variables from parameters
$LANG = $argv[1];

// Main directory of the PHP documentation (depends on the
// sapi used). We do need the trailing slash!
if ("cli" === php_sapi_name()) {
  if (isset($PHPDOCDIR) && is_dir($PHPDOCDIR)) {
    $DOCDIR = $PHPDOCDIR."/";
  } else {
    $DOCDIR = "./";
  }
} else {
  $DOCDIR = "../";
}
// =========================================================================
// Functions to get revision info and credits from a file
// =========================================================================

// Grabs the revision tag and stores credits from the file given
function get_tags($file, $val = "cvs-rev") {

  // Check for English CVS revision tag
  if ($val == "cvs-rev") {
    $cvsrev = substr (`svn propget cvs2svn:cvs-rev $file`, 2);
    return $cvsrev;
  }


  // Read the first 500 chars. The comment should be at
  // the begining of the file
  $fp = @fopen($file, "r") or die ("Unable to read $file.");
  $line = fread($fp, 500);
  fclose($fp);

  // No match before the preg
  $match = array();

  // Check for English SVN revision tag
  if ($val == "svn-rev") {
    preg_match ("/<!-- .Revision: (\d+) . -->/", $line, $match);

    return $match[1];
  }
  // Check for the translations "revision tag"

  preg_match ("/<!--.EN-Revision:\s*.*\.?(\d+)\s*Maintainer:/U", $line, $match);

  // Return with found revision number
  return $match[1];

} // get_tags() function end


// =========================================================================
// Functions to check file status in translated directory, and change revision
// =========================================================================

// Checks a file, and change revision
function change_revision($file) {

  global $DOCDIR, $LANG;

  $en_file = preg_replace("'^".$DOCDIR.$LANG."/'", $DOCDIR."en/", $file);

  // Get en file cvs revision
  $cvs_rev = get_tags($en_file);

  // Get en file cvs revision
  $svn_rev = get_tags($en_file, "svn-rev");

  // Get translated file revision
  $this_rev = get_tags($file, "this-rev");

  // If we have a numeric revision number (not n/a), compute rev. diff
  if (is_numeric($this_rev)) {

    $rev_diff = intval($cvs_rev) - intval($this_rev);

    if (!$rev_diff) {
      /* change revision number from cvs to svn */

      $line = file_get_contents($file);
      $str = "<!-- EN-Revision: $svn_rev Maintainer:";
      $newline = preg_replace("/<!--.EN-Revision:\s*.\d+\.\d+\s*Maintainer:/U", $str, $line);

      $fp = fopen($file, "w");
      fwrite ($fp, $newline);
      fclose($fp);

    } elseif ($rev_diff > 0) {
      /* change revision number to n/a */

      $line = file_get_contents($file);
      $str = "<!-- EN-Revision: n/a Maintainer:";
      $newline = preg_replace("/<!--.EN-Revision:\s*.\d+\.\d+\s*Maintainer:/U", $str, $line);

      $fp = fopen($file, "w");
      fwrite ($fp, $newline);
      fclose($fp);

    } // else no touch
  }

} // change_revision() function end

// =========================================================================
// A function to check directory status in translated directory
// =========================================================================

// Check the status of files in a diretory of phpdoc XML files
// The English directory is passed to this function to check
function get_dirs($dir) {

  global $DOCDIR;

  // Collect files and diretcories in these arrays
  $directories = array();
  $files       = array();

  // Open the directory
  $handle = @opendir($dir);

  // Walk through all names in the directory
  while ($file = @readdir($handle)) {

    if (
    (!is_dir($dir.'/' .$file) && !in_array(substr($file, -3), array('xml','ent')) && substr($file, -13) != 'PHPEditBackup' )
    || strpos($file, 'entities.') === 0
    || $file == 'translation.xml'
    || $file == 'README'
    || $file == 'DO_NOT_TRANSLATE'
    || $file == 'rsusi.txt'
    || $file == 'missing-ids.xml'
    || $file == 'license.xml'
    || $file == 'versions.xml'
    ) {
      continue;
    }

    if ($file != '.' && $file != '..' && $file != '.svn' && $dir != '/functions') {
      if (is_dir($dir.'/' .$file)) {
          $directories[] = $file;
      } elseif (is_file($dir.'/' .$file)) {
          $files[] = $file;
      }
    }

  }

  // Close the directory
  @closedir($handle);

  // Sort files and directories
  sort($directories);
  sort($files);

  // Go through files first
  foreach ($files as $file) {
    change_revision($dir.$file);
  }

  // Then go through subdirectories
  foreach ($directories as $file) {
    get_dirs($dir.$file.'/');
  }

} // get_dirs() function end


// =========================================================================
// Start of the program execution
// =========================================================================

// Check for directory validity
if (!@is_dir($DOCDIR . $LANG)) {
  die("The $LANG language code is not valid");
}

// Get all files status
get_dirs($DOCDIR.$LANG."/");

?>
