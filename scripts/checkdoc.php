#!/usr/bin/php -q
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
  | Authors:    Stig Bakken <ssb@php.net> (originally in Perl)           |
  |             Gabor Hojtsy <goba@php.net>                              |
  +----------------------------------------------------------------------+
*/

if ($argc > 2 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

Check documented functions in phpdoc

  Usage:
  <?php echo $argv[0];?> [missing]

  By providing the optional "missing" parameter,
  only a list of undocumented functions is listed,
  otherwise a full status report is printed.
  
  This program depends on ../funclist.txt as the
  list of functions compiled from the PHP source.
<?php
  exit;
}

// CONFIG SECTION
$docdir  = "../en/functions";
$funclist = "../funclist.txt";

/*********************************************************************/
/* Nothing to modify below this line                                 */
/*********************************************************************/

// Documented functions list
$func_documented = array();

// Functions in PHP (from funclist.txt)
$func_in_php = array();

// Longest function name for display
$longest = 0;

/*********************************************************************/
/* Here starts the functions part                                    */
/*********************************************************************/

// Checks a diretory of phpdoc XML files
function check_dir($dir, &$functions)
{
    // Collect files and diretcories in these arrays
    $directories = array();
    $files = array();

    // Open and traverse the directory
    $handle = @opendir($dir);
    while ($file = @readdir($handle)) {

      // Collect XML files
      if (strstr($file, ".xml")) {
        $files[] = $file;
      }

    }
    @closedir($handle);

    // Sort and check files
    sort($files);
    foreach ($files as $file) {
      check_file($dir, $file, $functions);
    }

} // check_dir() function end

function check_file ($dirname, $filename, &$functions)
{
    // Read in file contents
    $contents = preg_replace("/[\r\n]/", "", join("", file($dirname.$filename)));
    
    // Find all functions defined in this file
    preg_match_all("!id\s*=\s*([\"'])(function|class)\.([^\\1]+)\\1!U", $contents, $ids_found);

    // No ids found in file
    if (count($ids_found[3]) == 0) { return; }
    
    // Put functions into function list
    foreach ($ids_found[3] as $id) {
        $functions[str_replace("-", "_", $id)] = $filename;
    }
    ksort($functions);

} // check_file() function end
  
// Parse funclist.txt file for function names
function parse_funclist($funclist, &$longest, &$functions)
{
    // Read in file, initialize longest
    $file_lines = file($funclist);
    
    // Go through all lines, and find function names
    foreach ($file_lines as $line) {
        $line = trim($line);
        $length = strlen($line);
        // Not a comment, and contains a function name
        if ($line[0] != "#" && $length > 0) {
            $functions[] = $line;
            if ($length > $longest) { $longest = $length; }
        }
    }
    sort($functions);
    $functions = array_unique($functions);

} // parse_funclist() function end

/*********************************************************************/
/* Here starts the program                                           */
/*********************************************************************/

// Start with searching header
echo "Searching in $docdir for XML files...\n";
    
// Check the requested directory
check_dir("$docdir/", $func_documented);

// Process $funclist for PHP functions
parse_funclist($funclist, $longest, $func_in_php);
   
if ($argv[1] == "missing") {
    $undocumented = array_diff($func_in_php, array_keys($func_documented));
    echo "Functions in PHP source but not in documentation:\n\n";
    foreach ($undocumented as $func) {
      echo $func . "\n";
    }
} else {
    printf("%-{$longest}s    %s\n", "FUNCTION NAME", "DOCUMENTED IN");
    printf("%'-70s\n", '');
    foreach ($func_in_php as $function) {
        printf("%-{$longest}s    %s\n", $function, $func_documented[$function]);
    }
    $n_functions = count($func_in_php);
    $n_documented = count($func_documented);
    $percent_done = intval(($n_documented * 100) / $n_functions);

    printf("\n%d of %d functions documented (%d%% done, %d%% remaining).\n",
       $n_documented, $n_functions, $percent_done, 100-$percent_done);
}

echo "Possible documentation errors coming:\n\n";
$is_error = FALSE;
foreach ($func_documented as $func => $file) {
    if (!in_array($func, $func_in_php)) {
        echo "  $func in $file but not in $funclist\n";
        $is_error = TRUE;
    }
}
if (!$is_error) { echo "No documented but not implemented functions found.\n"; }

echo "Done!";

?>
