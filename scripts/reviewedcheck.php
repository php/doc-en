#!/usr/bin/php -q
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Yannick Torrès <yannick@php.net>
  +----------------------------------------------------------------------+

  $Id$
*/

if ( isset($argv[1]) && $argv[1] == '--help' || $argc < 2 ) {
?>

Check the actual status of reviewed files against
the actual langage xml files, and print statistics

  Usage:
  <?php echo $argv[0]; ?> <language-code> [><reviewedcheck.html>]

  <language-code> must be a valid language code used
  in the repository.

  If you specify ><reviewedcheck.html>, the output is an html file.

  Authors: Yannick Torrès <yannick@php.net>

<?php
  exit;
}

$LANG = $argv[1];
$date = date('r');

$path_doc = './'.$LANG.'/';

if( !is_dir($path_doc) ) {
 echo 'Directory for the lang "'.$LANG.'" don\'t exist !'."\r\n";
 exit;
}

if( $LANG == 'en' ) {
 echo 'You can\'t use this script for EN documentation.'."\r\n";
 exit;
}

$nb_no_tag = 0;
$nb_reviewed_no = 0;
$nb_reviewed_yes = 0;

$result = array();

// Scan the documentation
function check_doc($dir) {
global $path_doc;

if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
         if( is_file($dir.$file) && substr($file,0,1) != '.' ) {

if (
 $file == "rsusi.txt"
 || $file == "extensions.xml"
 || $file == "TRADUCTIONS.txt"
 || $file == "README"
 || $file == "Translators"
 || $file == "LISEZ_MOI.txt"
 || $file == "contributors.xml"
 || $file == "contributors.ent"
 || $file == "reserved.constants.xml"
 || $file == "DO_NOT_TRANSLATE"
 || strpos($dir, '/internals/')
 || ($file == "functions.xml" && strpos($dir, '/reference/')))
continue;

           check_tag($dir, $file);


         } elseif( is_dir($dir.$file) && $file != '.' && $file != '..' && $file != 'CVS' && $file != '.svn') {
           check_doc($dir.$file.'/');
         }
       }
       closedir($dh);
   }
}

} // fin check_dir


check_doc($path_doc);


function check_tag($dir, $file) {
global $result;
global $nb_no_tag;
global $nb_reviewed_no;
global $nb_reviewed_yes;

    // Read the first 500 chars. The comment should be at
    // the begining of the file
    $fp = @fopen($dir.$file, "r") or die ("Unable to read $dir.$file.");
    $line = fread($fp, 500);
    fclose($fp);

    if( preg_match("/<!--\s*Reviewed:\s*(.*?)*-->/Ui", $line, $match) ) {

     if( trim($match[1]) == 'no' ){
      $result['reviewed_no'][$dir][] = $file;
      $nb_reviewed_no++;
     } else if( trim($match[1]) == 'yes' ){
      $result['reviewed_yes'][$dir][] = $file;
      $nb_reviewed_yes++;
     }


    } else {
      $result['no_tag'][$dir][] = $file;
      $nb_no_tag++;
    }


} //check_tag


// Sort the result
if (isset($result['reviewed_no']) ) {
    ksort($result['reviewed_no']); 
}
if (isset($result['no_tag'])) {
    ksort($result['no_tag']);
}

// Rpint résult

$navbar = '<p class="c"><a href="#no_tag">File without tag</a> | <a href="#reviewed_no">Files with Reviewed\'tags to No</a></p>'."\n";

$nb_file = $nb_reviewed_yes + $nb_reviewed_no + $nb_no_tag;

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title>PHPDOC Reviewed-check</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
<!--
body {font-family:Verdana,Arial,Helvetica,sans-serif; font-size:14px; margin:0px 0px 0px 0px; background-color:#F0F0F0;}
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
.r     { text-align:right }
.rb    { text-align:right; font-weight:bold; }
.c     { text-align:center }
div.head { width: 100%; background-color: #9999cc; text-align: center; margin: 0 0 10px 0; padding: 10px 0 5px 0;}
div.head p {font-size: 12px;}
table.mini { width: 450px; margin: 0 auto 0 auto; border-spacing: 1px; padding: 4px; font-size: 12px;}
table.maxi { width: 820px; margin: 0 auto 0 auto; border-spacing: 1px; padding: 4px; font-size: 12px;}

//-->
</style>
</head>
<body>
<div class="head">
<h2>Status of the reviewed PHP Manual</h2>
<p>Generated: '.$date.' &nbsp; / &nbsp; Language: '.$LANG.'</p>
</div>

<a name="filesummary"></a>
<table class="mini">
<tr class="blue">
<th>File status type</th>
<th>Number of files</th>
<th>Percent of files</th>
</tr>

<tr class="act">
<td>Reviewed to Yes</td>
<td class="c">'.$nb_reviewed_yes.'</td>
<td class="c">'.number_format(($nb_reviewed_yes*100)/$nb_file, 2, '.', '').'%</td>
</tr>

<tr class="old">
<td>Reviewed to No</td>
<td class="c">'.$nb_reviewed_no.'</td>
<td class="c">'.number_format(($nb_reviewed_no*100)/$nb_file, 2, '.', '').'%</td>
</tr>

<tr class="old">
<td>Without Reviewed\'s tag</td>
<td class="c">'.$nb_no_tag.'</td>
<td class="c">'.number_format(($nb_no_tag*100)/$nb_file, 2, '.', '').'%</td>
</tr>

<tr class="blue">
<th>Total</th>
<th>'.$nb_file.'</th>
<th>100%</th>
</tr>


</table>

'.$navbar.'
<a name="no_tag"></a>
<table class="maxi">
<tr class="blue">
<th>File without Reviewed tag - '.$nb_no_tag.'</th>
<th>Sizes<br />in kB</th>
</tr>';


if( is_array($result['no_tag']) ) {
// List files without reviewed's tag
while( list($key, $val) = each($result['no_tag']) ) {

echo '
<tr class="blue"><th colspan="2">'.$key.'</th></tr>
';

asort($val);

 while( list($k, $v) = each($val) ) {

$url = 'http://cvs.php.net/viewvc.cgi/' . preg_replace( "'^".$path_doc."'", 'phpdoc-'.$LANG.'/', $key.$v).'?view=markup';

echo '
<tr class="old">
 <td><a href="'.$url.'">'.$v.'</a></td>
 <td class="c">'.intval(filesize($key.$v)/1024).'</td>
</tr>
';

 } // end of for


} // end of while
} else {
echo '
<tr class="old">
 <td colspan="2" class="c">No file</td>
</tr>
';
}

echo '
</table>
'.$navbar.'
<a name="reviewed_no"></a>
<table class="maxi">
<tr class="blue">
<th>File with Reviewed = No - '.$nb_reviewed_no.'</th>
<th>Sizes<br />in kB</th>
</tr>
';

// List file with Reviewed's tag to no
if (isset($result['reviewed_no']) && count($result['reviewed_no']) > 0) {

    while( list($key, $val) = each($result['reviewed_no']) ) {

        echo '<tr class="blue"><th colspan="2">'.$key.'</th></tr>';

        asort($val);

        while( list($k, $v) = each($val) ) {

            $url = 'http://cvs.php.net/viewvc.cgi/' . preg_replace( "'^".$path_doc."'", 'phpdoc-'.$LANG.'/', $key.$v).'?view=markup';

            echo '<tr class="old"><td><a href="'.$url.'">'.$v.'</a></td><td class="c">'.intval(filesize($key.$v)/1024).'</td></tr>';

        }
    }

    echo '</table></html>';
}
