#!/usr/bin/php -q
<?php

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
  
  The script will generate a revcheck.html file in
  the same dir, where the script runs, containing the
  information about translations
  
  Revision comment syntax for translated files:
  
   <!-- EN-Revision: 1.34 Maintainer: tom Status: ready -->

  If the revision number is not (yet) known:
   
   <!-- EN-Revision: n/a Maintainer: tom Status: working -->
   
  Written by Thomas Schöfbeck <tom@php.net>, 2001-08-11
  Adapted to phpdoc, developed further: <goba@php.net>

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
  $cvs_opt = "&f=h&num=10";

  /*********************************************************************/
  /* Nothing to modify below this line                                 */
  /*********************************************************************/

  // Long runtime
  set_time_limit(0);

  // Initializing arrays and vars
  $miss_file = array();
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


  // Checks a file, and gather stats
  function check_file($file, $file_cnt)
  {
    // The information is contained in these global arrays and vars
    global $miss_file, $miss_tag, $lang, $docdir, $r_alert, 
         $s_alert, $t_alert, $maintainer, $cvs_opt;

    // Translated file name check
    $t_file = preg_replace( "'^".$docdir."en/'", $docdir.$lang."/", $file );
    if (!file_exists($t_file)) {
      $miss_file[] = array(substr($t_file, strlen($docdir)+strlen($lang)), 
                 round(filesize($file)/1024, 1));
      return FALSE;
    }

    // Get translated files tag, with maintainer if needed
    if (empty($maintainer))
      $t_tag = get_tag($t_file, "\\S*");
    else
      $t_tag = get_tag($t_file, $maintainer);

    // No tag found
    if (count($t_tag) == 0) {
      $miss_tag[] = substr($t_file, strlen($docdir));
      return FALSE;
    }

    // Storing values for further processing
    $maint  = $t_tag[2];
    $en_rev = get_tag($file);

    // Make diff link if the revision is not n/a
    $t_td = substr($t_file, strlen($docdir)+strlen($lang)+1);
    if (is_numeric($t_tag[1])) {
      $r_diff = intval($en_rev) - intval($t_tag[1]);
      $t_rev  = "1.".$t_tag[1];
      if ($r_diff != 0) {
        $t_td   = "<a href=\"http://cvs.php.net/diff.php/".
                  preg_replace( "'^".$docdir."'", "phpdoc/", $file ).
                  "?r1=1.$t_tag[1]&r2=1.$en_rev$cvs_opt\">$t_td</a>";
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
      $col = "#68D888";
    } elseif ($r_diff == "n/a") {
      $col = "#f4a460 class=hl";
    } elseif ($r_diff >= $r_alert || $s_diff >= $s_alert || $t_diff <= $t_alert) {
      $col = "#ff0000 class=hl";
    } else {
      $col ="#eee8aa";
    }

    // Write out directory headline
    if ($file_cnt == 0) {
      $display_dir = preg_replace("'($docdir)(.+)/[^/]*$'", "\\2/", $t_file);
      print("<tr><th colspan=12 height=3 bgcolor=#666699>$display_dir</th></tr>");
    }
    
    // Write out the line for the file into the file
    print("<tr>\n <td bgcolor=$col>$t_td</td>\n".
          " <td bgcolor=$col> 1.$en_rev</td><td bgcolor=$col> $t_rev</td>\n".
          " <td bgcolor=$col align=right><b>$r_diff</b> &nbsp;</td>\n".
          " <td bgcolor=$col align=right>$en_size &nbsp;</td>\n".
          " <td bgcolor=$col align=right>$t_size &nbsp;</td>\n".
          " <td bgcolor=$col align=right><b>$s_diff</b> &nbsp;</td>\n".
          " <td bgcolor=$col align=right>$en_date &nbsp;</td>\n".
          " <td bgcolor=$col align=right>$t_date &nbsp;</td>\n".
          " <td bgcolor=$col align=right><b>$t_diff</b> &nbsp;</td>\n".
          " <td bgcolor=$col align=center>$maint</td>\n".
          " <td bgcolor=$col align=center>$t_tag[3]</td>\n</tr>\n");
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

  /*********************************************************************/
  /* Here starts the program                                           */
  /*********************************************************************/

  // Check for directory validity
  if (!@is_dir($docdir . $lang)) {
    die("The $lang language code is not valid");
  }  
  
  print("<html>
<head>
<title>PHPDOC Revision-check</title>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
<style type=\"text/css\"><!-- 
 h2 {font-family: arial,helvetica,sans-serif; color: #FFFFFF; font-size:28px; }
 td,a,p {font-family:arial,helvetica,sans-serif; color:#000000; font-size:14px; }
 th {font-family:arial,helvetica,sans-serif; color:#FFFFFF; font-size:14px; font-weight:bold; }
 .hl {font-family:arial,helvetica,sans-serif; color:#000000; font-size:14px; font-weight:bold; }
//-->
</style>
</head>
<body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 bgcolor=#F0F0F0>
<table width=100% border=0 cellspacing=0 bgcolor=#666699>
 <tr><td>
  <table width=100% border=0 cellspacing=1 bgcolor=#9999CC>
   <tr><td><h2 align=center>Status of the translated PHP Manual</h2>
      <p align=center style=\"font-size:12px; color:#FFFFFF;\">Generated: ".date("Y-m-d, H:i:s").
      " &nbsp; / &nbsp; Language: $lang<br>&nbsp;</p>
   </td></tr>
  </table>
 </td></tr>
</table> <br>
<table width=750 border=0 cellpadding=4 cellspacing=1 align=center>
 <tr>
  <th rowspan=2 bgcolor=#666699>Translated file</th>
  <th colspan=3 bgcolor=#666699>Revision</th>
  <th colspan=3 bgcolor=#666699>Size in kB</th>
  <th colspan=3 bgcolor=#666699>Age in days</th>
  <th rowspan=2 bgcolor=#666699>Maintainer</th>
  <th rowspan=2 bgcolor=#666699>Status</th>
 </tr>
 <tr>
  <th bgcolor=#666699>en</th>
  <th bgcolor=#666699>$lang</th>
  <th bgcolor=#666699>diff</th>
  <th bgcolor=#666699>en</th>
  <th bgcolor=#666699>$lang</th>
  <th bgcolor=#666699>diff</th>
  <th bgcolor=#666699>en</th>
  <th bgcolor=#666699>$lang</th>
  <th bgcolor=#666699>diff</th>
 </tr>
");

  // Check the English directory
  check_dir($docdir."en/");

  print("</table>\n<p>&nbsp;</p>\n");

  // Files without revision comment
  if (count($miss_tag) > 0) {
    print("<table width=350 border=0 cellpadding=3 cellspacing=1 align=center>\n".
          " <tr><th bgcolor=#666699><b>Files without Revision-comment:</b></th></tr>\n");
    foreach($miss_tag as $val) {
      print(" <tr><td bgcolor=#DDDDDD>&nbsp; $val</td></tr>\n");
    }
    print("</table>\n<p>&nbsp;</p>\n");
  }

  // Files not translated
  if (count($miss_file) > 0) {
    print("<table width=350 border=0 cellpadding=3 cellspacing=1 align=center>\n".
          " <tr><th colspan=2 bgcolor=#666699><b>Untranslated Files:</b></th></tr>\n");
    foreach($miss_file as $v) {
        print(" <tr><td bgcolor=#DDDDDD>&nbsp; en$v[0]</td>".
              "<td align=right bgcolor=#DDDDDD>$v[1] kB &nbsp;</td></tr>\n");
    }
    print("</table>\n<p>&nbsp;</p>\n");
  }

  // All OK, end the file
  print("</body>\n</html>\n");
  echo "Done!\n";

?>
