#!/usr/bin/php -q
<?php
/*
# +----------------------------------------------------------------------+
# | PHP Version 4                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2002 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP licience,     |
# | that is bundled with this package in the file LICENCE and is         |
# | avalible through the world wide web at                               |
# | http://www.php.net/license/2_02.txt.                                 |
# | If uou did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world wide web, please send a note to          |
# | license@php.net so we can mail you a copy immediately                |
# +----------------------------------------------------------------------+
# | Authors:    Thomas Schöfbeck <tom@php.net>                           |
# |             Gabor Hojtsy <goba@php.net>                              |
# |             Mark Kronsbein <mk@php.net>                              |
# +----------------------------------------------------------------------+
*/
if ($argc < 2 || $argc > 3) {
?>

Check the revision of translated files against
the actual english xml files, and print statistics

  Usage:
  <?=$argv[0]?> <language-code> [<maintainer>]

  <language-code> must be a valid language code used
  in the repository

  If you specify <maintainer>, the script only checks
  the files maintaned by the person you add here
  
  Read more about Revision comments and related
  funcionality in the PHP Documentation Howto.
   
  Authors: Thomas Schöfbeck <tom@php.net>
           Gabor Hojtsy <goba@php.net>
           Mark Kronsbein <mk@php.net> 

<?php
  exit;
}

// Long runtime
set_time_limit(0);

// A file is criticaly "outdated' if
define("ALERT_REV",   10); // translation is 10 or more revisions behind the en one
define("ALERT_SIZE",   3); // translation is  3 or more kB smaller than the en one
define("ALERT_DATE", -30); // translation is 30 or more days older than the en one

// Revision marks used to flag files
define("REV_UPTODATE", 1); // actual file
define("REV_NOREV",    2); // file with revision comment without revision
define("REV_CRITICAL", 3); // criticaly old / small / outdated
define("REV_OLD",      4); // outdated file
define("REV_NOTAG",    5); // file without revision comment
define("REV_NOTRANS",  6); // file without translation

define("REV_CREDIT",   7); // only used in translators list
define("REV_WIP",      8); // only used in translators list

// Colors used to mark files by status (colors for the above types)
$CSS = array(
  REV_UPTODATE => "act",
  REV_NOREV    => "norev",
  REV_CRITICAL => "crit",
  REV_OLD      => "old",
  REV_NOTAG    => "wip",
  REV_NOTRANS  => "wip",
  REV_CREDIT   => "wip",
  REV_WIP      => "wip",
);

// Option for the link to cvs.php.net: normal: "&f=h"
// long diff: "&f=h&num=10", unified (text): "&f=u"
define("CVS_OPT", "&amp;f=h&amp;num=10");

// Initializing variables from parameters
$LANG = $argv[1];
if ($argc == 3) {
    $MAINT = $argv[2];
} else {
    $MAINT = "";
}

// Main directory of the PHP documentation (one dir up
// in cvs). We do need the trailing slash!
$DOCDIR = "../";

$navbar = "<p class=c><a href=\"#intro\">Introduction</a> | " .
          "<a href=\"#translators\">Translators</a> | " .
          "<a href=\"#filesummary\">File summary by type</a> | " .
          "<a href=\"#files\">Files</a> | " .
          "<a href=\"#wip\">Work in progress</a> | " .
          "<a href=\"#misstags\">Missing revision numbers</a> | " .
          "<a href=\"#missfiles\">Untranslated files</a></p>\n";

  
// =========================================================================
// Functions to get revision info and credits from a file
// =========================================================================

// Grabs the revision tag from the file given
function get_tag($file, $val = "en-rev")
{

    // Read the first 500 chars. The comment should be at
    // the begining of the file
    $fp = @fopen($file, "r") or die ("Unable to read $file.");
    $line = fread($fp, 500);
    fclose($fp);

    // Check for English CVS revision tag (. is for $ in the preg!),
    // Return if this was needed (it should be there)
    if ($val == "en-rev") {
        preg_match("/<!-- .Revision: \d+\.(\d+) . -->/", $line, $match);
        return $match[1];
    }
    
    // No match before the preg
    $match = array();
    
    // Check for the translations "revision tag"
    preg_match ("/<!--\s*EN-Revision:\s*\d+\.(\d+)\s*Maintainer:\s*("
                . $val . ")\s*Status:\s*(.+)\s*-->/U", 
                $line,
                $match
    );

    // The tag with revision number is not found so search
    // for n/a revision comment (comment where revision is not known)
    if (count($match) == 0) {
        preg_match ("'<!--\s*EN-Revision:\s*(n/a)\s*Maintainer:\s*("
                    . $val . ")\s*Status:\s*(.+)\s*-->'U",
                    $line,
                    $match
        );
    }

    // Return with found revision info (number, maint, status)
    return $match;
    
} // get_tag() function end

// Grab CREDITS tag, the place to store previous credits
function get_credits ($file) {

    // Read the first 500 chars, the comment should be at
    // the begining of the file, if it is there
    $fp = @fopen($file, "r") or die ("Unable to read $file.");
    $line = fread($fp, 500);
    fclose($fp);
    
    // Try to find credits info in file, let more credits
    // then one, using commas as list separator
    if (preg_match("'<!--\s*CREDITS:\s*(.+)\s*-->'U",
                   $line,
                   $match)) {
      
      // Explode with commas a separators
      $credits = explode(",", $match[1]);
      
      // Trim all elements (let spaces to be
      // between credit info)
      foreach ($credits as $num => $credit) {
          $credits[$num] = trim($credit);
      }
      
      return $credits;

    } else {

      // No credits info in this file
      return array();
    }

} // get_credits() end

// =========================================================================
// Functions to check file status in translated directory, and store info
// =========================================================================

// Collect or return missing files depending on
// the parameters passed to this function
function missing_file()
{
    // Static var to store info, and return if asked
    static $missing_files = array();
    global $DOCDIR, $LANG;
    
    // Return with the missing files,
    // in case of no parameters to this function
    if (func_num_args() == 0) {
        return $missing_files;
    }
    
    // Get the parameters if we have them
    list($en_file, $trans_file) = func_get_args();
    
    // Compute new index for missing file (name without language dir)
    $index = substr($trans_file, strlen($DOCDIR) + strlen($LANG) + 1);
    
    // Compute new value for missing file (size and EN revision)
    $value = array(
        round(filesize($en_file)/1024, 1),
        "1." . get_tag($en_file)
    );
    
    // Push file into array
    $missing_files[$index] = $value;

} // missing_file() function end

// Collect or return files with missing tags depending on
// the parameters passed to this function
function missing_tag()
{
    // Static var to store info, and return if asked
    static $missing_tags = array();
    global $DOCDIR;
    
    // Return with the file with missing tags,
    // in case of no parameters to this function
    if (func_num_args() == 0) {
        return $missing_tags;
    }
    
    // Get the parameter if we have it
    list($trans_file, $en_size, $trans_size, $size_diff) = func_get_args();

    // Push data of file with missing tag onto the list
    $missing_tags[] = array(substr($trans_file, strlen($DOCDIR)), $en_size, $trans_size, $size_diff);
}

// Collect files by status mark
function files_by_mark()
{
    // Static var to store info, and return if asked
    static $files_by_mark = array();
    
    // Return with the file with missing tags,
    // in case of no parameters to this function
    if (func_num_args() == 0) {
        return $files_by_mark;
    }
    
    // Get the parameter if we have it
    list($mark, $inc) = func_get_args();

    // Add one to the count of this type of files
    $files_by_mark[$mark] += $inc;

}

// Collect files by maintainer and status mark
function files_by_maint()
{
    // Static var to store info, and return if asked
    static $files_by_maint = array();
    
    // Return with the file with missing tags,
    // in case of no parameters to this function
    if (func_num_args() == 0) {
        return $files_by_maint;
    }
    
    // Get the parameter if we have it
    list($mark, $maint) = func_get_args();

    // Add one to the maintainers files list,
    // especially counting this type of file
    $files_by_maint[$maint][$mark]++;

}

// Checks a file, and gather status info
function get_file_status($file)
{
    // The information is contained in these global arrays and vars
    global $DOCDIR, $LANG, $MAINT;
    
    // Transform english file name to translated file nme
    $trans_file = preg_replace("'^{$DOCDIR}en/'", "{$DOCDIR}{$LANG}/", $file);

    // If we cannot find the file, we push it into the missing files list
    if (!@file_exists($trans_file)) {
        files_by_mark(REV_NOTRANS, 1);
        missing_file($file, $trans_file);
        return FALSE;
    }

    // Get credits from file and collect it
    $this_credits = get_credits($trans_file);
    
    // Add credits to file by maintainer list
    foreach ($this_credits as $nick) {
        files_by_maint(REV_CREDIT, $nick);
    }

    // If we need to check for a specific translator
    if (!empty($MAINT)) {
        // Get translated files tag, with maintainer
        $trans_tag = get_tag($trans_file, $MAINT);

        // If this is a file belonging to another
        // maintainer, than we would not like to
        // deal with it anymore
        if (count($trans_tag) == 0) {
            $trans_tag = get_tag($trans_file, "\\S*");
            // We found a tag for another maintainer
            if (count($trans_tag) > 0) {
                return FALSE;
            }
        }
    }
    // No specific maintainer, check for a revision tag
    else {
        $trans_tag = get_tag($trans_file, "\\S*");
    }

    // Distribute values in separate vars for further processing
    list(, $this_rev, $this_maint, $this_status) = $trans_tag;

    // Get translated file name (without directories)
    $trans_name = substr($trans_file, strlen($DOCDIR) + strlen($LANG) + 1);
    
    // Get English file revision for revision computing
    $en_rev = get_tag($file);
    
    // If we have a numeric revision number (not n/a), compute rev. diff
    if (is_numeric($this_rev)) {
        $rev_diff   = intval($en_rev) - intval($this_rev);
        $trans_rev  = "1.{$this_rev}";
        $en_rev     = "1.{$en_rev}";
    } else {
      // If we have no numeric revision, make all revision
      // columns hold the rev from the translated file
      $rev_diff = $trans_rev = $this_rev;
      $en_rev   = "1.{$en_rev}";
    }

    // Compute sizes, times and diffs
    $en_size    = intval(filesize($file) / 1024);
    $trans_size = intval(filesize($trans_file) / 1024);
    $size_diff  = intval($en_size) - intval($trans_size);
    
    $en_date    = intval((time() - filemtime($file)) / 86400);
    $trans_date = intval((time() - filemtime($trans_file)) / 86400);
    $date_diff  = $en_date - $trans_date;

    // If we found no revision tag, then collect this
    // file in the missing tags list
    if (count($trans_tag) == 0) {
        files_by_mark(REV_NOTAG, 1);
        missing_tag($trans_file, $en_size, $trans_size, $size_diff);
        return FALSE;
    }

    // Make decision on file category by revision, date and size
    if ($rev_diff === 0) {
        $status_mark = REV_UPTODATE;
    } elseif ($rev_diff === "n/a") {
        $status_mark = REV_NOREV;
    } elseif ($rev_diff >= ALERT_REV || $size_diff >= ALERT_SIZE || $date_diff <= ALERT_DATE) {
        $status_mark = REV_CRITICAL;
    } else {
        $status_mark = REV_OLD;
    }
    
    // Store files by status, and by maintainer too
    files_by_mark ($status_mark, 1);
    files_by_maint($status_mark, $this_maint);
    
    return array(
        "full_name"  => $file,
        "short_name" => $trans_name,
        "revision"   => array($en_rev,  $trans_rev,  $rev_diff),
        "size"       => array($en_size, $trans_size, $size_diff),
        "date"       => array($en_date, $trans_date, $date_diff),
        "maintainer" => $this_maint,
        "status"     => $this_status,
        "mark"       => $status_mark
    );
        

    return TRUE;

} // get_file_status() function end

// =========================================================================
// A function to check directory status in translated directory
// =========================================================================

// Check the status of files in a diretory of phpdoc XML files
// The English directory is passed to this function to check
function get_dir_status($dir, $DOCDIR, $LANG)
{
    // If this is an old "functions" directory
    // (not under reference) then do not travers
    if (preg_match("!/en/functions!", $dir)) {
        return array();
    }
    
    // Collect files and diretcories in these arrays
    $directories = array();
    $files       = array();
    
    // Open the directory 
    $handle = @opendir($dir);
    
    // Walk through all names in the directory
    while ($file = @readdir($handle)) {

      // If we found a file with one or two point as a name,
      // or a CVS directory, skip the file
      if (preg_match("/^\.{1,2}/",$file) || $file == 'CVS')
        continue;

      // JUST TEMPORARY TILL THE <TRANSLATION>/REFERENCE/FUNCTIONS.XML - ISSUE IS CLARIFIED
      // If we found a file functions.xml in the
      // <lang>/reference/ tree, skip the file
      if ($file == "functions.xml" && preg_match("!/\w+/reference/\w+/!", $dir))
        continue;

      // Collect files and directories
      if (is_dir($dir.$file)) { $directories[] = $file; }
      else { $files[] = $file; }

    }
    
    // Close the directory
    @closedir($handle);
      
    // Sort files and directories
    sort($directories);
    sort($files);
      
    // Go through files first
    $dir_status = array();
    foreach ($files as $file) {
        // If the file status is OK, append the status info
        if ($file_status = get_file_status("{$dir}{$file}")) {
            $dir_status[] = $file_status;
        }
    }

    // Then go through subdirectories, merging all the info
    // coming from subdirs to one array
    foreach ($directories as $file) {
        $dir_status = array_merge(
            $dir_status, 
            get_dir_status("{$dir}{$file}/", $DOCDIR, $LANG)
        );
    }
    
    // Return with collected file info in
    // this dir and subdirectories [if any]
    return $dir_status;

} // get_dir_status() function end

// =========================================================================
// Functions to read in the translation.xml file and process contents
// =========================================================================

// Get a multidimensional array with tag attributes
function parse_attr_string ($tags_attrs)
{
    $tag_attrs_processed = array();

    // Go through the tag attributes
    foreach($tags_attrs as $attrib_list) {

      // Get attr name and values
      preg_match_all("!(.+)=\\s*([\"'])\\s*(.+)\\2!U", $attrib_list, $attribs);

      // Assign all attributes to one associative array
      $attrib_array = array();
      foreach ($attribs[1] as $num => $attrname) {
        $attrib_array[trim($attrname)] = trim($attribs[3][$num]);
      }

      // Collect in order of tags received
      $tag_attrs_processed[] = $attrib_array;

    }

    // Retrun with collected attributes
    return $tag_attrs_processed;

} // parse_attr_string() end

// Parse the translation.xml file for
// translation related meta information
function parse_translation($DOCDIR, $LANG, $MAINT)
{
    // Path to find translation.xml file, set default values,
    // in case we can't find the translation file
    $translation_xml = "{$DOCDIR}{$LANG}/translation.xml";
    $output_charset  = 'iso-8859-1';
    $translation     = array(
        "intro"    => "",
        "persons"  => array(),
        "files"    => array(),
        "allfiles" => array(),
    );
    
    // Check for file availability, return with default
    // values, if we cannot find the file
    if (!@file_exists($translation_xml)) {
        return array($output_charset, $translation);
    }
    
    // Else go on, and load in the file, replacing all
    // space type chars with one space
    $txml = join("", file($translation_xml));
    $txml = preg_replace("/\\s+/", " ", $txml);

    // Get intro text (different for a persons info and
    // for a whole group info page)
    if (!empty($MAINT)) {
        $translation["intro"] = "Personal Statistics for {$MAINT}";
    } else {
        preg_match("!<intro>(.+)</intro>!s", $txml, $match);
        $translation["intro"] = trim($match[1]);
    }
    
    // Get encoding for the output, from the translation.xml
    // file encoding (should be the same as the used encoding
    // in HTML)
    preg_match("!<\?xml(.+)\?>!U", $txml, $match);
    $xmlinfo = parse_attr_string($match);
    $output_charset = $xmlinfo[1]["encoding"];
    
    // Get persons list preg pattern, only check for a specific
    // maintainer, if the users asked for it
    if (!empty($MAINT)) {
        $pattern = "!<person([^<]+nick=\"{$MAINT}\".+)/\\s?>!U";
    } else {
        $pattern = "!<person(.+)/\\s?>!U";
    }
    
    // Find all persons matching the pattern
    preg_match_all($pattern, $txml, $matches);
    $translation['persons'] = parse_attr_string($matches[1]);
    
    // Get list of work in progress files
    if (!empty($MAINT)) {

        // Only check for a specific maintainer, if we were asked to
        preg_match_all("!<file([^<]+person=\"{$MAINT}\".+)/\\s?>!U", $txml, $matches);
        $translation['files'] = parse_attr_string($matches[1]);

        // Other maintainers wip files need to be cleared from
        // available files list in the future, so store that info too.
        preg_match_all("!<file(.+)/\\s?>!U", $txml, $matches);
        $translation['allfiles'] = parse_attr_string($matches[1]);
        
        // Provide info about number of WIP files
        files_by_mark(REV_WIP, count($translation['allfiles']));
        
    } else {
        
        // Get all wip files
        preg_match_all("!<file(.+)/\\s?>!U", $txml, $matches);
        $translation['files'] = parse_attr_string($matches[1]);

        // Provide info about number of WIP files
        files_by_mark(REV_WIP, count($translation['files']));

    }
    
    // Return with collected info in two vars
    return array($output_charset, $translation);

} // parse_translation() function end()

// =========================================================================
// Debug functions for all the functions and code on this page
// =========================================================================

// Print preformatted (debug function)
function print_pre($var)
{
    print("<pre>");
    print_r($var);
    print("</pre>");
} // print_pre() function end

// =========================================================================
// Start of the program execution
// =========================================================================

// Check for directory validity
if (!@is_dir($DOCDIR . $LANG)) {
    die("The $LANG language code is not valid");
}
  
// Parse translation.xml file for more information
list($charset, $translation) = parse_translation($DOCDIR, $LANG, $MAINT);

// Add WIP files to maintainers file count
foreach ($translation["files"] as $num => $fileinfo) {
    files_by_maint(REV_WIP, $fileinfo["person"]);
}

// Get all files status
$files_status = get_dir_status("{$DOCDIR}en/", $DOCDIR, $LANG);

// Get missing files and files with missing
// tags collected in the previous step
$missing_files  = missing_file();
$missing_tags   = missing_tag();

// Files counted by mark and maintainer
$files_by_mark  = files_by_mark();
$files_by_maint = files_by_maint();

// Figure out generation date
$date = date("r");
  
// =========================================================================
// Start of HTML page
// =========================================================================

print <<<END_OF_MULTILINE
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title>PHPDOC Revision-check</title>
<meta http-equiv="Content-Type" content="text/html; charset={$charset}">
<style type="text/css">
<!--
h2,td,a,p,a.ref,th { font-family:Arial,Helvetica,sans-serif; font-size:14px; }
h2,th,a.ref { color:#FFFFFF; }
td,a,p { color:#000000; }
h2     { font-size:28px; }
th     { font-weight:bold; }
.blue  { background-color:#666699; }
.act   { background-color:#68D888; }
.norev { background-color:#f4a460; }
.old   { background-color:#eee8aa; }
.crit  { background-color:#ff6347; }
.wip   { background-color:#dcdcdc; }
.miss  { background-color:#dddddd; }
.r     { text-align:right }
.c     { text-align:center }
body   { margin:0px 0px 0px 0px; background-color:#F0F0F0; }
//-->
</style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" bgcolor="#666699">
<tr><td>
<table width="100%" border="0" cellspacing="1" bgcolor="#9999CC">
<tr><td><h2 class=c>Status of the translated PHP Manual</h2><p class=c style="font-size:12px;">Generated: {$date} &nbsp; / &nbsp; Language: $LANG<br></p></td></tr>
</table>
</td></tr>
</table>
END_OF_MULTILINE;

print ($navbar);

// =========================================================================
// Intro block goes here
// =========================================================================

// If we have an introduction text, print it out, with an anchor
if (!empty($translation["intro"])) {
    print '<a name="intro"></a>';
    print '<table width="800" align="center"><tr><td class=c>' .
           $translation['intro'] . '</td></tr></table><p></p>';
}

// =========================================================================
// Translators table goes here
// =========================================================================

// If person list available (valid translation.xml file in lang), print out
// the person list, with respect to the maintainer parameter specified
if (!empty($translation["persons"])) {

print <<<END_OF_MULTILINE
<a name="translators"></a>
<table width="820" border="0" cellpadding="4" cellspacing="1" align="center">
<tr>
<th rowspan="2" class="blue">Translator's name</th>
<th rowspan="2" class="blue">Contact email</th>
<th rowspan="2" class="blue">Nick</th>
<th rowspan="2" class="blue">C<br>V<br>S</th>
<th colspan="7" class="blue">Files maintained</th>
</tr>
<tr>
<th class="{$CSS[REV_CREDIT]}" style="color:#000000">cre-<br>dits</th>
<th class="{$CSS[REV_UPTODATE]}" style="color:#000000">upto-<br>date</th>
<th class="{$CSS[REV_OLD]}" style="color:#000000">old</th>
<th class="{$CSS[REV_CRITICAL]}" style="color:#000000">cri-<br>tical</th>
<th class="{$CSS[REV_NOREV]}" style="color:#000000">no<br>rev</th>
<th class="{$CSS[REV_WIP]}" style="color:#000000">wip</th>
<th class="blue">sum</th>
</tr>
END_OF_MULTILINE;

    // ' Please leave this comment here

    // We will collect the maintainers by nick here
    $maint_by_nick = array();
    
    // Print out a line for each maintainer (with respect to
    // maintainer setting provided in command line)
    foreach($translation["persons"] as $num => $person) {
        
        // Do not print out this person, if a
        // specific maintainer info is asked for
        if (!empty($MAINT) && $person["nick"] != $MAINT) {
            continue;
        }
        
        // Put maintaner number into associative array
        // [Used in further tables for referencing]
        $maint_by_nick[$person["nick"]] = $num;
        
        // Decide on the CVS text and the color of the line
        if ($person["cvs"] === "yes") {
            $cvsu = "x";
            $col = "old";
        } else {
            $cvsu = "&nbsp;";
            $col = "wip";
        }
        
        // Try to do some antispam actions
        $person["email"] = str_replace(
            "@",
            "<small>:at:</small>",
            $person["email"]
        );
        
        // Get file info for this person
        if (isset($files_by_maint[$person["nick"]])) {
            $pi = $files_by_maint[$person["nick"]];
        } else {
            $pi = array();
        }
        
        print("<tr class=$col>" .
              "<td><a name=\"maint$num\">$person[name]</a></td>" .
              "<td>$person[email]</td>" .
              "<td>$person[nick]&nbsp;</td>" .
              "<td class=c>$cvsu</td>" .
              "<td class=c>" . $pi[REV_CREDIT]   . "&nbsp;</td>" .
              "<td class=c>" . $pi[REV_UPTODATE] . "&nbsp;</td>" .
              "<td class=c>" . $pi[REV_OLD]      . "&nbsp;</td>" .
              "<td class=c>" . $pi[REV_CRITICAL] . "&nbsp;</td>" .
              "<td class=c>" . $pi[REV_NOREV]    . "&nbsp;</td>" .
              "<td class=c>" . $pi[REV_WIP]      . "&nbsp;</td>" .
              "<th class=blue>" . array_sum($pi)    . "</th>" .
              "</tr>\n");
     }
  
    print "</table>\n<p>&nbsp;</p>\n";
} 

// =========================================================================
// Files summary table goes here
// =========================================================================

// Do not print out file summary table, if we are printing out a page
// for only one maintainer (his personal summary is in the table above)
if (empty($MAINT)) {

print <<<END_OF_MULTILINE
<a name="filesummary"></a>
<table width="450" border="0" cellpadding="4" cellspacing="1" align="center">
<tr>
<th class="blue">File status type</th>
<th class="blue">Number of files</th>
<th class="blue">Percent of files</th>
</tr>
END_OF_MULTILINE;

    $files_sum = array_sum($files_by_mark);
    
    $file_types = array(
      array (REV_UPTODATE, "Up to date files"),
      array (REV_OLD,      "Old files"),
      array (REV_CRITICAL, "Critical files"),
      array (REV_WIP,      "Work in progress"),
      array (REV_NOREV,    "Files without revision number"),
      array (REV_NOTAG,    "Files without revision tag"),
      array (REV_NOTRANS,  "Files available for translation")
    );
    
    foreach ($file_types as $num => $type) {
        $type[] = 'class="' . $CSS[$type[0]] . '"';
        $type[] = intval($files_by_mark[$type[0]]);
        $type[] = number_format(
            $files_by_mark[$type[0]] * 100 / $files_sum, 2
        );
    
print <<<END_OF_MULTILINE
<tr>
 <td {$type[2]}>{$type[1]}</td>
 <td {$type[2]} align="center">{$type[3]}</td>
 <td {$type[2]} align="center">{$type[4]}%</td>
</tr>
END_OF_MULTILINE;

    }

print <<<END_OF_MULTILINE
<tr>
<th class="blue">Files total</th>
<th class="blue">{$files_sum}</th>
<th class="blue">100%</th>
</tr>
END_OF_MULTILINE;

    print("</table>\n<p>&nbsp;</p>\n");

}

print ($navbar."<p>&nbsp;</p>\n");


// =========================================================================
// Files table goes here
// =========================================================================

print <<<END_OF_MULTILINE
<a name="files"></a>
<table width="820" border="0" cellpadding="4" cellspacing="1" align="center">
<tr>
<th rowspan="2" class="blue">Translated file</th>
<th colspan="3" class="blue">Revision</th>
<th colspan="3" class="blue">Size in kB</th>
<th colspan="3" class="blue">Age in days</th>
<th rowspan="2" class="blue">Maintainer</th>
<th rowspan="2" class="blue">Status</th>
</tr>
<tr>
<th class="blue">en</th>
<th class="blue">$LANG</th>
<th class="blue">diff</th>
<th class="blue">en</th>
<th class="blue">$LANG</th>
<th class="blue">diff</th>
<th class="blue">en</th>
<th class="blue">$LANG</th>
<th class="blue">diff</th>
</tr>
END_OF_MULTILINE;

// This was the previous directory [first]
$prev_dir = $new_dir = "{$DOCDIR}en";

// Go through all files collected
foreach ($files_status as $num => $file) {

    // Do not print out actual files
    if ($file["mark"] == REV_UPTODATE) {
        continue;
    }
    
    // Make the maintainer a link, if we have that maintainer in the list
    if (isset($maint_by_nick[$file["maintainer"]])) {
      $file["maintainer"] = '<a href="#maint' . $maint_by_nick[$file["maintainer"]] .
                            '">' . $file["maintainer"] . '</a>';
    }

    // If we have a 'numeric' revision diff and it is not zero,
    // make a link to the CVS repository's diff script
    if ($file["revision"][2] != "n/a" && $file["revision"][2] !== 0) {
        $file["short_name"] = "<a href=\"http://cvs.php.net/diff.php/" .
                              preg_replace( "'^".$DOCDIR."'", "phpdoc/", $file["full_name"]) .
                              "?r1=" . $file["revision"][1] . 
                              "&amp;r2=" . $file["revision"][0] .
                              CVS_OPT . "\">" . basename($file["short_name"]) . "</a>";
    } else {
        // Else just shorten the filename (we have directory headers)
        $file["short_name"] = basename($file["short_name"]);
    }

    // Guess the new directory from the full name of the file
    $new_dir = substr($file["full_name"], 0, strrpos($file["full_name"], "/"));
    
    // If this is a new directory, put out dir headline
    if ($new_dir != $prev_dir) {
        
        // Drop out the unneded parts from the dirname...
        $display_dir = str_replace("{$DOCDIR}en/", "", dirname($file["full_name"]));
        
        // Print out directory header
        print "<tr class=blue><th colspan=\"12\" height=\"3\">$display_dir</th></tr>\n";
        
        // Store the new actual directory
        $prev_dir = $new_dir;
    }
    
    // Write out the line for the current file (get file name shorter)
    print "<tr class={$CSS[$file['mark']]}><td>{$file['short_name']}</td>".
          "<td> {$file['revision'][0]}</td>" .
          "<td> {$file['revision'][1]}</td>".
          "<td class=r><b>{$file['revision'][2]}</b> </td>".
          "<td class=r>{$file['size'][0]} </td>".
          "<td class=r>{$file['size'][1]} </td>".
          "<td class=r><b>{$file['size'][2]}</b> </td>".
          "<td class=r>{$file['date'][0]} </td>".
          "<td class=r>{$file['date'][1]} </td>".
          "<td class=r><b>{$file['date'][2]}</b> </td>".
          "<td class=c>{$file['maintainer']}</td>".
          "<td class=c>".trim($file['status'])."</td></tr>\n";

}

print("</table>\n<p>&nbsp;</p>\n$navbar<p>&nbsp;</p>\n");


// =========================================================================
// Work in progress table goes here
// =========================================================================

// If work-in-progress list is available (valid translation.xml file in lang)
if (count($translation["files"]) != 0) {

    // Figure out, if we need to use optional date and
    // revision columns (if there is no file with that parameter,
    // we won't include the table column for that)
    $using_date = FALSE; $using_rev = FALSE;
    foreach ($translation["files"] as $file) {
        if (isset($file["date"]))     { $using_date = TRUE; }
        if (isset($file["revision"])) { $using_rev = TRUE; }
    }
  
    // Print out files table header
    print "<a name=\"wip\"></a>\n" .
    "<table width=\"820\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" align=\"center\">\n" .
    "<tr>".
    "<th class=\"blue\">Work in progress files</th>".
    "<th class=\"blue\">Translator</th>".
    "<th class=\"blue\">Type</th>";
  
    // Print out date and revision columns if needed
    if ($using_date) {
        print '<th class="blue">Date</th>' . "";
    }
    if ($using_rev) {
        print '<th class="blue">CO-Revision</th>' .
              '<th class="blue">EN-Revision</th>';
    }
    print "</tr>\n";
  
    // Go through files, and print out lines for them
    foreach($translation["files"] as $num => $finfo) {
    
        // If we have a valid maintainer, link to the summary
        if (isset($maint_by_nick[$finfo["person"]])) {
            $finfo["person"] = '<a href="#maint' . $maint_by_nick[$finfo["person"]] .
                               '">' . $finfo["person"] . '</a>';
        }
       
        // Print out the line with the first columns
        print "<tr class=miss><td>$finfo[name]</td>" .
              "<td>$finfo[person]</td><td>$finfo[type]</td>";

        // If we need the date column, print it out
        if ($using_date) {
            print "<td>$finfo[date]</td>";
        }

        // If we need the revision column, print it out
        if ($using_rev) {
            print "<td>$finfo[revision]</td><td>" .
                  $missing_files[$finfo["name"]][1] .
                  "</td>";
        }
      
        // End the line
        print "</tr>\n";

        // Collect files in WIP list
        $wip_files[$finfo["name"]] = TRUE;
    } 
  
    print "</table>\n<p>&nbsp;</p>\n$navbar<p>&nbsp;</p>\n";
    
} 

// Files translated, but without a revision comment
$count = count($missing_tags);
if ($count > 0) {
    print "<a name=\"misstags\"></a>" .
          "<table width=\"400\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\">\n".
          "<tr class=blue><th rowspan=2><b>Files without Revision-comment ($count files):</b></th>".
          "<th colspan=3>Sizes in kB</th></tr>\n".
          "<tr class=blue><th>en</th><th>$LANG</th><th>diff</th></tr>\n";
    foreach($missing_tags as $val) {
        // Shorten the filename (we have directory headers)
        $short_file = basename($val[0]);

        // Guess the new directory from the full name of the file
        $new_dir = substr($val[0], 0, strrpos($val[0], "/"));
    
        // If this is a new directory, put out dir headline
        if ($new_dir != $prev_dir) {
        
            // Print out directory header
            print "<tr class=blue><th colspan=4>".dirname($val[0])."</th></tr>\n";
        
            // Store the new actual directory
            $prev_dir = $new_dir;
        }
        print "<tr class=miss><td>$short_file</td><td class=r>$val[1]</td>".
              "<td class=r>$val[2]</td><td class=r>$val[3]</td></tr>\n";
    }
    print "</table>\n<p>&nbsp;</p>\n$navbar<p>&nbsp;</p>\n";

}

// Merge all work in progress files collected
$wip_files = array_merge(
    $translation["files"],    // Files for this translator
    $translation["allfiles"]  // Files for all the translators
);

// Delete wip entires from available files list
foreach ($wip_files as $file) {
    if (isset($missing_files[$file['name']])) {
        unset($missing_files[$file['name']]);
    }
}

// Files not translated and not "wip"
$count = count($missing_files);
if ($count > 0) {
    print "<a name=\"missfiles\"></a>" .
          "<table width=\"400\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\">\n" .
          "<tr><th class=blue><b><a name=\"avail\" class=\"ref\">" .
          " Available for translation</a> ($count files):</b></th><th class=blue><b>kB</b></th></tr>\n";
    foreach($missing_files as $file => $info) {
        // Shorten the filename (we have directory headers)
        $short_file = basename($file);

        // Guess the new directory from the full name of the file
        $new_dir = substr($file, 0, strrpos($file, "/"));
    
        // If this is a new directory, put out dir headline
        if ($new_dir != $prev_dir) {
        
            // Print out directory header if not "."
            print "<tr class=blue><th colspan=\"2\">".dirname($file)."</th></tr>\n";
        
            // Store the new actual directory
            $prev_dir = $new_dir;
        }

        print "<tr class=miss><td>$short_file</td>" .
              "<td class=r>$info[0]</td></tr>\n";
    }
    print "</table>\n<p>&nbsp;</p>\n$navbar<p>&nbsp;</p>\n";

}

// All OK, end the file
print "</body>\n</html>\n";

?>