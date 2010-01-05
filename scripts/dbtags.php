#!/usr/bin/php -q
<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2010 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Gabor Hojtsy <goba@php.net>                              |
  +----------------------------------------------------------------------+
 
  $Id$
*/

if ($argc > 2 || (isset($argv[1]) && in_array($argv[1], array('--help', '-help', '-h', '-?')))) {
?>

Process XML files for used DocBook tags
and give statistics

  Usage:
  <?php echo $argv[0];?> [<language-code>]

  <language-code> can be a valid language code
  used in the repository, or 'all' for all
  languages. Defaults to en.

  The script will generate a tag_usage.txt
  file, containing the tags used and the numbers.

<?php
  exit;
}

// CONFIG SECTION
$docdir = "../"; // Main directory of the PHP documentation (one dir up in cvs)

/*********************************************************************/
/* Nothing to modify below this line                                 */
/*********************************************************************/

// Long runtime
set_time_limit(0);

// Array to collect the entities
$used_tags = array();

// Default values
$langcodes = array("en");

// Parameter value copying
if ($argc == 2) { 
    $langcodes = array($argv[1]);
    if ($argv[1] === 'all') {
        $langcodes = array("ar", "cs", "de", "en", "es", "fr",
                           "hk", "hu", "it", "ja", "kr", "nl",
                           "pl", "pt_BR", "ru", "tr", "tw");
    }
}

/*********************************************************************/
/* Here starts the functions part                                    */
/*********************************************************************/

// Checks a directory of phpdoc XML files
function check_dir($dir, &$used_tags)
{
    // Collect files and directories in these arrays
    $directories = array();
    $files = array();

    // Open and traverse the directory
    $handle = @opendir($dir);
    while ($file = @readdir($handle)) {

      // Collect directories and XML files
      if ($file != 'CVS' && $file != '.' &&
          $file != '..' && is_dir($dir.$file)) {
        $directories[] = $file;
      }
      elseif (strstr($file, ".xml")) {
        $files[] = $file;
      }

    }
    @closedir($handle);

    // Sort files and directories
    sort($directories);
    sort($files);

    // Files first...
    foreach ($files as $file) {
      check_file($dir.$file, $used_tags);
    }

    // than the subdirs
    foreach ($directories as $file) {
      check_dir($dir.$file."/", $used_tags);
    }
} // check_dir() function end

function check_file ($filename, &$used_tags)
{
    // Read in file contents
    $contents = preg_replace("/[\r\n]/", "", join("", file($filename)));
    
    // Drop out CDATA sections, they do not contain any DocBook tags
    $contents = preg_replace("/<!\\[CDATA\\[.+\\]\\]>/U", "", $contents);
    
    // Drop out comments, they do not contain any DocBook tags
    $contents = preg_replace("/<!--.+-->/U", "", $contents);

    // Find all tags in this file
    preg_match_all("!<([^\\s>/]+)[\\s>]!U", $contents, $tags_found);
    
    // No entities found
    if (count($tags_found[1]) == 0) { return; }
    
    // New occurrences found, so increase the number
    foreach ($tags_found[1] as $tag_name) {
        if (isset($used_tags[$tag_name])) {
            $used_tags[$tag_name]++;
        } else {
            $used_tags[$tag_name] = 1;
        }
    }

} // check_file() function end
  
/*********************************************************************/
/* Here starts the program                                           */
/*********************************************************************/

// Checking all languages
foreach ($langcodes as $langcode) {

    // Check for directory validity
    if (!@is_dir($docdir . $langcode)) {
        print("The $langcode language code is not valid\n");
        continue;
    } else {
        $tested_trees[] = $langcode;
    }
      
    // If directory is OK, start with the header
    echo "Searching in $docdir$langcode ...\n";
    
    // Check the requested directory
    check_dir("$docdir$langcode/", $used_tags);

}
    
echo "Generating tag_usage.txt ...\n";
    
$fp = fopen("tag_usage.txt", "w");
fwrite($fp, "TAG USAGE STATISTICS

=========================================================
In this file you can find tag usage stats compiled
from the following tree[s] at phpdoc:\n" .
join(", ", $tested_trees) . ".

You may find some rarely used tags here, and find out
what tags others use to write documentation.
=========================================================

");

arsort($used_tags);
foreach ($used_tags as $tag_name => $number) {
    fwrite($fp, sprintf("%-30s %d", $tag_name, $number). "\n");
}

fclose($fp);

echo "Done!\n";

?>
