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
  
  Revision comment syntax for translated files:
  
   <!-- EN-Revision: 1.34 Maintainer: tom Status: ready -->

  If the revision number is not (yet) known:
   
   <!-- EN-Revision: n/a Maintainer: tom Status: working -->
   
  Read more about Revision comments and related
  funcionality in the PHP Documentation Howto.
   
  Authors: Thomas Schöfbeck <tom@php.net>
           Gabor Hojtsy <goba@php.net>
           Mark Kronsbein <mk@php.net> 

<?php
  exit;
}

  // CONFIG SECTION
  $docdir = "../"; // Main directory of the PHP documentation (one dir up in cvs)

  // Warn with red color if
  $r_alert = 10;   // the translation is $r_alert or more revisions behind the en one
  $s_alert = 10;   // the translation is $s_alert or more kB smaller than the en one
  $t_alert = 30;   // the translation is $t_alert or more days older than the en one

  // Option for the link to the cvs.php.net: normal: "&f=h"
  // long diff: "&f=h&num=10", unified (text): "&f=u"
  $cvs_opt = "&amp;f=h&amp;num=10";

  /*********************************************************************/
  /* Nothing to modify below this line                                 */
  /*********************************************************************/

  // Long runtime
  set_time_limit(0);

  // Initializing arrays and vars
  $missed_files = array();
  $miss_tag  = array();
  $t_alert   = -1 * $t_alert;
  $lang      = $argv[1];
  if ($argc == 3) { $maintainer = $argv[2]; }
  else { $maintainer = ""; }
  
  /*********************************************************************/
  /* Here starts the functions part                                    */
  /*********************************************************************/

  // Grabs the revision tag from the file given
  function get_tag($file, $val = "en-rev")
  {

    // Read the first 200 chars, the comment should be at
    // the begining of the file
    $fp = @fopen($file, "r") or die ("Unable to read $file.");
    $line = fread($fp, 200);
    fclose($fp);

    // Checking for english CVS revision tag
    if ($val=="en-rev") {
      preg_match("/<!-- .Revision: \d+\.(\d+) . -->/", $line, $match);
      return ($match[1]);
    }
    // Checking for the translations "revision tag"
    else {
      preg_match ("/<!--\s*EN-Revision:\s*\d+\.(\d+)\s*Maintainer:\s*(".$val.
            ")\s*Status:\s*(.+)\s*-->/", $line, $match);
    }
    // The tag with revision number is not found so search
    // for n/a revision comment
    if (count($match) == 0) {
      preg_match ("'<!--\s*EN-Revision:\s*(n/a)\s*Maintainer:\s*(".$val.
            ")\s*Status:\s*(.+)\s*-->'", $line, $match);
    }
    return ($match);
  } // get_tag() function end
  
  // Grab CREDITS tag, the place to store previous credits
  function get_credits ($file) {

    // Read the first 300 chars, the comment should be at
    // the begining of the file
    $fp = @fopen($file, "r") or die ("Unable to read $file.");
    $line = fread($fp, 300);
    fclose($fp);
    
    if (preg_match("'<!--\s*CREDITS:\s*(.+)\s*-->'U",
                   $line, $match)) {
      return (explode(",", $match[1]));
    } else {
      return array();
    }
  } // get_credits() end

  // Checks a file, and gather stats
  function check_file($file, $file_cnt)
  {
    // The information is contained in these global arrays and vars
    global $missed_files, $miss_tag, $lang, $docdir, $r_alert, 
         $s_alert, $t_alert, $maintainer, $cvs_opt, $plist,
         $personinfo;

    // Translated file name check
    $t_file = preg_replace( "'^".$docdir."en/'", $docdir.$lang."/", $file );
    if (!file_exists($t_file)) {
      $missed_files[substr($t_file, strlen($docdir)+strlen($lang)+1)] = 
        array(round(filesize($file)/1024, 1), get_tag($file));
      return FALSE;
    }

    // Get translated files tag, with maintainer if needed
    if (!empty($maintainer)) {
      $t_tag = get_tag($t_file, $maintainer);
      // Don't count other's Tags as missing
      if (count($t_tag) == 0) {
        $t_tag = get_tag($t_file, "\\S*");
        if (count($t_tag) > 0)
          return FALSE;
      }
    }
    else
      $t_tag = get_tag($t_file, "\\S*");

    // No tag found
    if (count($t_tag) == 0) {
      $miss_tag[] = substr($t_file, strlen($docdir));
      return FALSE;
    }

    // Storing values for further processing
    $maint  = $t_tag[2];
    $en_rev = get_tag($file);
    
    // Make the maintainer a link
    if (isset($plist[$maint])) {
      $maintd = '<a href="#maint' . $plist[$maint] . '">' . $maint . '</a>';
    } else {
      $maintd = $maint;
    }
    
    // Get credits comment, and add to array
    $credits = get_credits($t_file);
    foreach ($credits as $nick) {
      $personinfo[trim($nick)]["credits"]++;
    }

    // Make diff link if the revision is not n/a
    $t_td = substr($t_file, strlen($docdir)+strlen($lang)+1);
    if (is_numeric($t_tag[1])) {
      $r_diff = intval($en_rev) - intval($t_tag[1]);
      $t_rev  = "1.".$t_tag[1];
      if ($r_diff != 0) {
        $t_td   = "<a href=\"http://cvs.php.net/diff.php/".
                  preg_replace( "'^".$docdir."'", "phpdoc/", $file ).
                  "?r1=1.$t_tag[1]&amp;r2=1.$en_rev$cvs_opt\">$t_td</a>";
      }
    } else {
      $r_diff = $t_rev = $t_tag[1];
    }

    // Compute sizes, times and diffs
    $en_size = intval(filesize($file) / 1024);
    $t_size  = intval(filesize($t_file) / 1024);
    $s_diff  = intval($en_size) - intval($t_size);
    $en_date = intval((time() - filemtime($file)) / 86400);
    $t_date  = intval((time() - filemtime($t_file)) / 86400);
    $t_diff  = $en_date - $t_date;

    // Taging actuality of files with colors
    if ($r_diff === 0) {
      $col = "\"#68D888\"";
      $personinfo[$maint]["actual"]++;
    } elseif ($r_diff == "n/a") {
      $col = "\"#f4a460\" class=\"hl\"";
      $personinfo[$maint]["norev"]++;
    } elseif ($r_diff >= $r_alert || $s_diff >= $s_alert || $t_diff <= $t_alert) {
      $col = "\"#ff0000\" class=\"hl\"";
      $personinfo[$maint]["veryold"]++;
    } else {
      $col ="\"#eee8aa\"";
      $personinfo[$maint]["old"]++;
    }

    // Write out directory headline
    if ($file_cnt == 0) {
      $display_dir = preg_replace("'($docdir)(.+)/[^/]*$'", "\\2/", $t_file);
      print("<tr><th colspan=\"12\" height=\"3\" bgcolor=\"#666699\">$display_dir</th></tr>");
    }
    
    // Write out the line for the file into the file
    print("<tr>\n <td bgcolor=$col>$t_td</td>\n".
          " <td bgcolor=$col> 1.$en_rev</td><td bgcolor=$col> $t_rev</td>\n".
          " <td bgcolor=$col align=\"right\"><b>$r_diff</b>&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\">$en_size&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\">$t_size&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\"><b>$s_diff</b>&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\">$en_date&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\">$t_date&nbsp;</td>\n".
          " <td bgcolor=$col align=\"right\"><b>$t_diff</b>&nbsp;</td>\n".
          " <td bgcolor=$col align=\"center\">$maintd&nbsp;</td>\n".
          " <td bgcolor=$col align=\"center\">$t_tag[3]&nbsp;</td>\n</tr>\n");
    return TRUE;
  } // check_file() end


  // Checks a diretory of phpdoc XML files
  function check_dir($dir)
  {
    global $docdir, $lang;
    
    // Collect files and diretcories in these arrays
    $directories = array();
    $files = array();
    
    // Open and traverse the directory
    $handle = @opendir($dir);
    while ($file = @readdir($handle)) {
      if (preg_match("/^\.{1,2}/",$file) || $file == 'CVS')
        continue;

      // Collect files and directories
      if (is_dir($dir.$file)) { $directories[] = $file; }
      else { $files[] = $file; }

    }
    @closedir($handle);
      
    // Sort files and directories
    sort($directories);
    sort($files);
      
    // Files first...
    $file_cnt = 0;
    foreach ($files as $file) {
      if (check_file($dir.$file, $file_cnt))  { $file_cnt++; }
    }

    // than the subdirs
    foreach ($directories as $file) {
      check_dir($dir.$file."/");
    }
  } // check_dir() end
  
  // Get a multidimensional array with tag attributes
  function get_attr_array ($tags_attrs)
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
    return $tag_attrs_processed;
  } // get_attr_array() end
  
  // Print preformatted (debug function)
  function print_pre($var)
  {
    print("<pre>");
    print_r($var);
    print("</pre>");
  } // print_pre() end 

  /*********************************************************************/
  /* Here starts the program                                           */
  /*********************************************************************/

  // Check for directory validity
  if (!@is_dir($docdir . $lang)) {
    die("The $lang language code is not valid");
  }
  
  // Used regexps here for parsing to be compatible with
  // non XML compatible PHP setups
  $translation_xml = $docdir . $lang . "/translation.xml";
  $output_charset = 'iso-8859-1';
  $translation = array();
  if (@file_exists($translation_xml)) {
    $txml = join("", file($translation_xml));
    $txml = preg_replace("/\\s+/", " ", $txml);

    // Get intro text
    if (!empty($maintainer))
        $translation["intro"] = "Personal Statistics for ".$maintainer;
    else {
        preg_match("!<intro>(.+)</intro>!s", $txml, $match);
        $translation["intro"] = trim($match[1]);
    }
    
    // Get encoding for the output
    preg_match("!<\?xml(.+)\?>!U", $txml, $match);
    $xmlinfo = get_attr_array($match);
    $output_charset = $xmlinfo[1]["encoding"];
    
    // Get persons list
    if (!empty($maintainer))
        $pattern = "!<person([^<]+nick=\"$maintainer\".+)/\\s?>!U";
    else
        $pattern = "!<person(.+)/\\s?>!U";
    preg_match_all($pattern, $txml, $matches);
    $translation['persons'] = get_attr_array($matches[1]);
    foreach($translation['persons'] as $num => $person) {
        $plist[$person["nick"]] = $num;
    }
    $personinfo = array();

    // Get files list
    if (!empty($maintainer)) {
        preg_match_all("!<file([^<]+person=\"$maintainer\".+)/\\s?>!U", $txml, $matches);
        $translation['files'] = get_attr_array($matches[1]);
        // get all others to clear out from available files
        preg_match_all("!<file(.+)/\\s?>!U", $txml, $matches);
        $wip_others = get_attr_array($matches[1]);
    }
    else {
        preg_match_all("!<file(.+)/\\s?>!U", $txml, $matches);
        $translation['files'] = get_attr_array($matches[1]);
    }
  }

  print("
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd\">
<html>
<head>
<title>PHPDOC Revision-check</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$output_charset\">
<style type=\"text/css\"><!-- 
 h2 {font-family: Arial,Helvetica,sans-serif; color: #FFFFFF; font-size: 28px; }
 td,a,p {font-family: Arial,Helvetica,sans-serif; color: #000000; font-size: 14px; }
 a.ref {font-family: Arial,Helvetica,sans-serif; color: #FFFFFF; font-size: 14px; }
 th {font-family: Arial,Helvetica,sans-serif; color: #FFFFFF; font-size: 14px; font-weight: bold; }
.hl {font-family: Arial,Helvetica,sans-serif; color: #000000; font-size: 14px; font-weight: bold; }
body {margin-left: 0px; margin-right: 0px; margin-top: 0px; margin-bottom: 0px; }
//-->
</style>
</head>
<body bgcolor=\"#F0F0F0\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" bgcolor=\"#666699\">
<tr><td>
  <table width=\"100%\" border=\"0\" cellspacing=\"1\" bgcolor=\"#9999CC\">
   <tr><td><h2 align=\"center\">Status of the translated PHP Manual</h2>
      <p align=\"center\" style=\"font-size:12px; color:#FFFFFF;\">Generated: ".date("r").
      " &nbsp; / &nbsp; Language: $lang<br>&nbsp;</p>
   </td></tr>
  </table>
</td></tr>
</table> <br>");

if (isset($translation["intro"])) {
    print('<table width="800" align="center"><tr><td>' . $translation['intro'] . '</td></tr></table><p></p>');
}

ob_start();

print("
<table width=\"820\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" align=\"center\">
<tr>
  <th rowspan=\"2\" bgcolor=\"#666699\"><a name=\"translated\" class=\"ref\">Translated file</a></th>
  <th colspan=\"3\" bgcolor=\"#666699\">Revision</th>
  <th colspan=\"3\" bgcolor=\"#666699\">Size in kB</th>
  <th colspan=\"3\" bgcolor=\"#666699\">Age in days</th>
  <th rowspan=\"2\" bgcolor=\"#666699\">Maintainer</th>
  <th rowspan=\"2\" bgcolor=\"#666699\">Status</th>
</tr>
<tr>
  <th bgcolor=\"#666699\">en</th>
  <th bgcolor=\"#666699\">$lang</th>
  <th bgcolor=\"#666699\">diff</th>
  <th bgcolor=\"#666699\">en</th>
  <th bgcolor=\"#666699\">$lang</th>
  <th bgcolor=\"#666699\">diff</th>
  <th bgcolor=\"#666699\">en</th>
  <th bgcolor=\"#666699\">$lang</th>
  <th bgcolor=\"#666699\">diff</th>
</tr>
");

// Check the English directory
check_dir($docdir."en/");

print("</table>\n<p>&nbsp;</p>\n");

// If work-in-progress available (valid translation.xml file in lang)
if (isset($translation["files"])) {
  $using_date = FALSE; $using_rev = FALSE;
  foreach ($translation["files"] as $file) {
    if (isset($file["date"])) { $using_date = TRUE; }
    if (isset($file["revision"])) { $using_rev = TRUE; }
  }
  print("
  <table width=\"820\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" align=\"center\">
  <tr>
   <th bgcolor=\"#666699\"><a name=\"wip\" class=\"ref\">Work in progress files</a></th>
   <th bgcolor=\"#666699\">Translator</th>
   <th bgcolor=\"#666699\">Type</th>
  ");
  if ($using_date) { print ("<th bgcolor=\"#666699\">Date</th>\n"); }
  if ($using_rev) { print ("<th bgcolor=\"#666699\">CO-Revision</th><th bgcolor=\"#666699\">EN-Revision</th>\n"); }
  print("</tr>\n");
  
  foreach($translation["files"] as $num => $finfo) {
    if (isset($plist[$finfo["person"]])) {
      $maintd = '<a href="#maint' . $plist[$finfo["person"]] . '">' . $finfo["person"] . '</a>';
    } else { 
      $maintd = $finfo["person"];
    }
    print("<tr bgcolor=\"#DDDDDD\"><td>$finfo[name]</td>" .
          "<td>$maintd</td><td>$finfo[type]</td>");
    if ($using_date) { print("<td>$finfo[date]</td>"); }
    if ($using_rev) { print("<td>$finfo[revision]</td><td>1." . $missed_files[$finfo["name"]][1] . "</td>"); }
    print("</tr>");
    $personinfo[$finfo["person"]]["wip"]++;
    $wip_files[$finfo["name"]] = TRUE;
 }
  
  print ("</table>\n<p>&nbsp;</p>\n");
} 

$file_lists = ob_get_contents();
ob_end_clean();

// If person list available (valid translation.xml file in lang)
if (isset($translation["persons"])) {
  print("
  <table width=\"820\" border=\"0\" cellpadding=\"4\" cellspacing=\"1\" align=\"center\">
  <tr>
   <th rowspan=\"2\" bgcolor=\"#666699\"><a name=\"translators\" class=\"ref\">Translator's name</a></th>
   <th rowspan=\"2\" bgcolor=\"#666699\">Contact email</th>
   <th rowspan=\"2\" bgcolor=\"#666699\">Nick</th>
   <th rowspan=\"2\" bgcolor=\"#666699\">CVS</th>
   <th colspan=\"6\" bgcolor=\"#666699\">Files maintained</th>
  </tr>
  <tr>
   <th bgcolor=\"#666699\">credits</th>
   <th bgcolor=\"#666699\">actual</th>
   <th bgcolor=\"#666699\">old</th>
   <th bgcolor=\"#666699\">veryold</th>
   <th bgcolor=\"#666699\">norev</th>
   <th bgcolor=\"#666699\">wip</th>
  </tr>
  ");
  
  foreach($translation["persons"] as $num => $person) {
    if ($person["cvs"] === "yes") { $cvsu = "yes"; $col = "\"#eee8aa\""; }
    else { $cvsu = "no"; $col = "\"#dcdcdc\""; }
    $person["email"] = str_replace("@", "<small>:at:</small>", $person["email"]);
    $pi = $personinfo[$person["nick"]];
    print("<tr bgcolor=$col><td><a name=\"maint$num\">$person[name]</a></td>" .
          "<td>$person[email]&nbsp;</td><td>$person[nick]&nbsp;</td><td>$cvsu&nbsp;</td>" .
          "<td align=\"center\">$pi[credits]&nbsp;</td><td align=\"center\">$pi[actual]&nbsp;</td>".
          "<td align=\"center\">$pi[old]&nbsp;</td><td align=\"center\">$pi[veryold]&nbsp;</td>".
          "<td align=\"center\">$pi[norev]&nbsp;</td><td align=\"center\">$pi[wip]&nbsp;</td></tr>\n");
   }
  
  print ("</table>\n<p>&nbsp;</p>\n");
} 

print($file_lists);

// Files without revision comment
$count = count($miss_tag);
if ($count > 0) {
  print("<table width=\"440\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\">\n".
        " <tr><th bgcolor=\"#666699\"><b>Files without Revision-comment ($count files):</b></th></tr>\n");
  foreach($miss_tag as $val) {
    print(" <tr><td bgcolor=\"#DDDDDD\">&nbsp; $val</td></tr>\n");
  }
  print("</table>\n<p>&nbsp;</p>\n");
}

// Clear out work in progress files from available files
if (isset($wip_files)) {
  foreach($wip_files as $fname => $one) {
    if (isset($missed_files[$fname])) { unset($missed_files[$fname]); }
  }
}
if (isset($wip_others)) {
  foreach($wip_others as $file => $one) {
      if (isset($missed_files[$one['name']])) { unset($missed_files[$one['name']]); }
  }
}

// Files not translated and not "wip"
$count = count($missed_files);
if ($count > 0) {
  print("<table width=\"440\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" align=\"center\">\n".
        " <tr><th colspan=\"2\" bgcolor=\"#666699\"><b><a name=\"avail\" class=\"ref\">Available for translation</a> ($count files):</b></th></tr>\n");
  foreach($missed_files as $file => $info) {
      print(" <tr><td bgcolor=\"#DDDDDD\">&nbsp; $file</td>".
            "<td align=\"right\" bgcolor=\"#DDDDDD\">$info[0] kB &nbsp;</td></tr>\n");
  }
  print("</table>\n<p>&nbsp;</p>\n");
}

// All OK, end the file
print("</body>\n</html>\n");

?>