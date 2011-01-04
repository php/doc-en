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
  | Authors : Salah Faya <visualmind@php.net>                            |
  +----------------------------------------------------------------------+

  $Id$
*/
//-- The PHPDOC Online XML Editing Tool 
//--- Purpose: this is the actual xml editor script

//------- Initialization
require 'base.php';
$user = sessionCheck('?from=editxml');

$lang = $user['phpdocLang'];
$translationPath = $phpdocLangs[$lang]['DocCVSPath'];


//------- Frames split 
if (isset($_REQUEST['split'])) {
	if ($_REQUEST['split']=='false' || !$_REQUEST['split']) $_REQUEST['split'] = false;
	$_SESSION['split'] = $_REQUEST['split'];
}

if (!empty($_SESSION['split']) && !isset($_REQUEST['noframes'])) {
	$source = $_REQUEST['source'];
	$file = $_REQUEST['file'];	

	// ToDo automatically show the translated file in one frame and the source in another
	print "<frameset cols=*,*><frame name=frame1 src='editxml.php?file=$file&source=$source&noframes=1'><frame name=frame2 src='editxml.php?file=$file&source=$source&noframes=1'></frameset>";
	exit;
}


//------- File Request
if (isset($_REQUEST['source']) && isset($_REQUEST['file'])) {
	$source = $_REQUEST['source'];
	$file = $_REQUEST['file'];
}

if (!$file || strstr($file, '..')) {
	exit;
}

$requestedFilename = $file;


// Turn on/off XML as Text
if (!empty($_REQUEST['textedit']) && $_REQUEST['textedit']!='false') {
	$_SESSION['textedit'] = true;
} else {
	$_SESSION['textedit'] = false;
}

// Turn on/off Hiding XML tags when editing
if (!empty($_REQUEST['hidexml']) && $_REQUEST['hidexml']!='false') {
	$_SESSION['hidexml'] = true;
} else {
	$_SESSION['hidexml'] = false;
}

// Turn on/off Line By Line
if (!empty($_REQUEST['linebyline']) && $_REQUEST['linebyline']!='false') {
	$_SESSION['para'] = false;
} else {
	$_SESSION['para'] = true;
}



//------- Functions 
// Parse XML to find editable text 
// this function is written poorly but it does the job
function myParse($text) {
	$results = array();
	$rc = 0;

	for($i=0; $i<strlen($text); $i++) {
		$c = substr($text, $i, 1);

		if ($c=='<') {
			// Starting tag
			$tagC = '';
			$tag = true;
		}

		if ($tag) {
			$tagC .= $c;
		} else {
			if ($c=="\n") {
				// Advance the counter so each line is stored separately 
				if (empty($_SESSION['para'])) { 
					$rc++;
				} else {					
					if (!isset($results[$rc])) {
						// Get position of New editable text
						$results[$rc] = array('index'=>$i, 'text'=>'');
					}
					$results[$rc]['text'] .= $c;
				}
			} else {
				if (!isset($results[$rc])) {
					// Get position of New editable text
					$results[$rc] = array('index'=>$i, 'text'=>'');
				}
				$results[$rc]['text'] .= $c;
			}
		}
		if ($c=='>') {
			// Tag ending
			$tagC .= $c;
			$tag = false;
			$rc++;
		}
	}

	// Filter editable text
	$resultsx = array();
	foreach($results as $resline) {		
		$res = trim($resline['text']);

		// Skip empty, &xx; and literals 
		if (!$res || ($res[0]=='&' && substr($res,-1)==';') || isLiteral($res) ) continue;
		
		if (!empty($_SESSION['para'])) {
			// trim \n and adjust position
			while(substr($resline['text'], 0, 1)=="\n" || substr($resline['text'], 0, 1)=="\r") {
				$resline['index']++;
				$resline['text'] = substr($resline['text'], 1);
			}
			// recheck \n 
			if (!strstr($resline['text'], "\n")) {
				// treat as one line (trim and adjust)
				while(substr($resline['text'], 0, 1)==' ') {
					$resline['index']++;
					$resline['text'] = substr($resline['text'], 1);
				}
				$resline['text'] = trim($resline['text']);
			}
			$res = $resline['text'];
		} else {
			// trim and adjust position
			while(substr($resline['text'], 0, 1)==' ') {
				$resline['index']++;
				$resline['text'] = substr($resline['text'], 1);
			}
			$res = trim($resline['text']);
		}
		$resultsx[$resline['index']] = $res;
	}

	// Return an array contains all editable texts and their positions in file contents
	return $resultsx;
}


function isLiteral($text) {
	// ToDo check if text is literal and don't need to be translated
	return false;
}



//------- Locate file according to the selected path

$status = getTranslationStatus($file);

switch($source) {
	case 'upath': // User cached path
		$file = $user['cache'].getCacheName($file);	
	break;
	case 'apath': // File from translation
		$file = $translationPath.$file;
	break;
	case 'epath': // English source
		$file = CVS_ROOT_PATH . 'en/' . $file;
	break;
	case 'diff': // cvs diff
		if ($status['distance']>0) {
			$url = "http://cvs.php.net/viewvc.cgi/phpdoc/en/$file?r1=$status[fileEnRevision]&r2=$status[lastEnRevision]";
			$html = implode('', file($url));

			preg_match_all("#(<table cellspacing=\"0\" cellpadding=\"0\">\\s*<tr class=\"vc_diff_header\">.+</table>)#s", $html, $parts);
			$data = '<meta name="generator" content="ViewVC 1.1-dev" /><link rel="stylesheet" href="viewvc-style.css" type="text/css" /><div style="font-family: Arial;">';
			$data .= str_replace('/viewvc.cgi/', 'http://cvs.php.net/viewvc.cgi/', $parts[1][0]).'</div>';
		}
	break;
}


//------- Prepare the file for editing

if ($source!='diff'): 
// Read the file contents
$data = implode('', file($file));


if (empty($_SESSION['textedit'])) {

// Protect CDATA tags by hiding them before parsing
preg_match_all("#<\!\[CDATA\[(.+)\]\]>#Us", $data, $matches);
$cdataStore = array();
$cdataMarks = array();
	foreach($matches[0] as $i=>$cdata) {		
		$mark = "<CDATA-$i-></CDATA>";
		$cdl = strlen($cdata)-strlen($mark);
		$mark = "<CDATA-$i-".str_repeat('X', $cdl).'></CDATA>';
		$cdataStore[$i] = $cdata;
		$cdataMarks[$i] = $mark;
		$data = str_replace($cdata, $mark, $data);
	}

// Parse file contents and fetch editable text
$results = myParse($data);

// Keep a copy of the actual contents
$newdata = $data;

// Replace editable text with special Marks
$results = array_reverse($results, true);

foreach($results as $index=>$text) {
	$newtext = $text;

	// If in saving process Put submitted text instead of current text
	if (isset($_POST['textAt'][$index])) {
		$newtext = stripslashes2($_POST['textAt'][$index]);

		// Fix paragraphs that lost prefix \n 
		if (strstr($newtext, "\n") && substr($newtext, 0, 1)!="\n") {
			//$newtext = "\n$newtext";
		}

		$results[$index] = $newtext;
		$newdata = substr_replace($newdata, $newtext, $index, strlen($text));
		$updated = true;
	}	

	// Put special marks
	if ($newtext!="'>" && $newtext!='">') {
		if (strstr($newtext, "\n")) {
			// This marks text that has new line (so we will use multiline textarea)
			$data = substr_replace($data, "_PMRK[$index]{".$newtext."}PMRK_", $index, strlen($text));
		} elseif (strstr($newtext, "'") && !strstr($newtext, '"')) {
			// This marks text that has single quote (so the input field will use double quote)
			$data = substr_replace($data, "_ZMRK[$index]{".$newtext."}ZMRK_", $index, strlen($text));
		} elseif (strstr($newtext, "'") && strstr($newtext, '"')) {
			// This marks text that has single and double quote (so we will use textarea)
			$data = substr_replace($data, "_YMRK[$index]{".$newtext."}YMRK_", $index, strlen($text));
		} else {
			// This marks text that can use a normal input
			$data = substr_replace($data, "_XMRK[$index]{".$newtext."}XMRK_", $index, strlen($text));
		}
	}
}

if (!empty($_SESSION['para']) && !empty($updated)) {
	// Remove \r (some browsers)
	$newdata = str_replace("\r\n", "\r", $newdata);
	$newdata = str_replace("\n", "\r", $newdata);
	$newdata = str_replace("\r", "\n", $newdata);
	if (substr($newdata, -1)!="\n") {
		$newdata .= "\n";
	}
}

// Put CData back
$data = str_replace($cdataMarks, $cdataStore, $data);
$newdata = str_replace($cdataMarks, $cdataStore, $newdata);

} else {
	// Edit XML as Text
	if (isset($_POST['xmldata'])) {
		$newdata = stripslashes2($_POST['xmldata']);

		// Remove \r (some browsers)
		$newdata = str_replace("\r\n", "\r", $newdata);
		$newdata = str_replace("\n", "\r", $newdata);
		$newdata = str_replace("\r", "\n", $newdata);
		if (substr($newdata, -1)!="\n") {
			$newdata .= "\n";
		}
		$updated = true;
	}
}


//------- Save or Send file when requested
if (!empty($updated) || !empty($_REQUEST['download'])) {

	// Charset for new translated files
	if ($phpdocLangs[$lang]['charset']!='iso-8859-1') {
		$charset = $phpdocLangs[$lang]['charset'];
		$newdata = str_replace('<?xml version="1.0" encoding="iso-8859-1"?>', '<?xml version="1.0" encoding="'.$charset.'"?>', $newdata);
	}

	// EN-Revision for new translated files
	if (!stristr($newdata, 'EN-Revision:')) {
		$newdata = str_replace('<!-- $'.'Revision'.': ', '<!-- \$'.'Revision'.": 1 $ -->\n<!-- EN-Revision: ", $newdata);
	}

	// Send file as is (download)
	if (isset($_REQUEST['download'])) {
		header('Content-Type: application/download');
		header('Content-Disposition: attachment; filename='.basename($requestedFilename));
		print $newdata;
		exit;
	}	

	if ($requireLogin) {
		// Save file in user cache folder 
		$f = fopen($user['cache'].getCacheName($requestedFilename), 'w');
		fputs($f, $newdata);
		fclose($f);

		// Simple log
		$f = fopen($user['cache']."files.txt", 'a');
		$time = getDateTimeToLog();
		$ip = getUserIP();
		fputs($f, "$time|$ip|$requestedFilename\r\n");
		fclose($f);	
	}

	exit("File has been saved in your cache folder. You can now <a href='editxml.php?file=$requestedFilename&source=$source&download=yes&noframes=1'>download it</a> or edit another file");
}


if (empty($_SESSION['textedit'])) {

	// Mark XML Tags to be hidden (when hidexml is true)
	$hideXMLTag = empty($_SESSION['hidexml']) ? '' : $_SESSION['hidexml'];
	$startXMLTag = '';
	$endXMLTag   = '';

	if ($hideXMLTag) {
		$startXMLTag = '_TMRK(';
		$endXMLTag = ')TMRK_';
	}


	// Replace special marks with <input> or <textarea>
	$data = htmlspecialchars($data);
	$data = preg_replace("#_XMRK\[(\\d+)\]\{(.+)\}XMRK_#U", "$endXMLTag<input type=text class=xinput ondblclick='dblclk(this)' onmouseover='irn(\\1)' onmouseout='iro(\\1)' onkeyup='ku(\\1, event)' onkeydown='kp(\\1, event)' name=textAt[\\1] value='\\2'>$startXMLTag", $data);
	$data = preg_replace("#_YMRK\[(\\d+)\]\{(.+)\}YMRK_#U", "$endXMLTag<textarea rows=1 class=xinput ondblclick='dblclk(this)' onmouseover='irn(\\1)' onmouseout='iro(\\1)' onkeyup='ku(\\1, event)' onkeydown='kp(\\1, event)' name=textAt[\\1]>\\2</textarea>$startXMLTag", $data);
	$data = preg_replace("#_ZMRK\[(\\d+)\]\{(.+)\}ZMRK_#U", "$endXMLTag<input type=text class=xinput ondblclick='dblclk(this)' onmouseover='irn(\\1)' onmouseout='iro(\\1)' onkeyup='ku(\\1, event)' onkeydown='kp(\\1, event)' name=textAt[\\1] value=\"\\2\">$startXMLTag", $data);
	$data = preg_replace("#_PMRK\[(\\d+)\]\{(.+)\}PMRK_#Us", "$endXMLTag<br><textarea name=textAt[\\1] class=xinput2 ondblclick='dblclk(this)' onmouseover='irn(\\1)' onmouseout='iro(\\1)' style='width: 80%;'>\\2</textarea><br>$startXMLTag", $data);
	$data = $startXMLTag.$data.$endXMLTag;

	// Update input size according to text length
	foreach($results as $index=>$text) {
		if (!strstr($text, "\n")) {
			$size = strlen(utf8_decode($text));		
			$data = str_replace("textAt[$index]", "textAt[$index] size=$size cols=$size", $data);
		} else {
			$size = substr_count($text, "\n")+1;
			$data = str_replace("<textarea name=textAt[$index]", "<textarea name=textAt[$index] rows=$size", $data);
		}
	}

	// Hide XML Tags
	if ($hideXMLTag) {
		preg_match_all("#_TMRK\((.+)\)TMRK_#Us", $data, $matches);
		foreach($matches[0] as $i=>$line) {
			$r = ' ';
			if (strstr($line, "\n")) {
				$r = "\n";
				if (substr_count($line, "\n")>3) {
					$r.="\n";
				}
			}
			$data = str_replace($line, $r, $data);
		}
	}

} 

endif;


//------- Output

?>
<html>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $phpdocLangs[$lang]['charset']; ?>" />
<body bgcolor="#9999cc">
<style type="text/css">
	body  {
		font-family: Tahoma;
		font-size: 12px; 
	}
	.xinput {
		font-family: <?php print $phpdocLangs[$lang]['font']; ?>; 
		border: 0px none;
		color: #AA0000; 
		border-bottom: 1px dotted gray;
		}
	.xinput2 {
		font-family: <?php print $phpdocLangs[$lang]['font']; ?>;
		color: #AA0000;; 
		}
	.editbox {
		font-family: <?php print $phpdocLangs[$lang]['font']; ?>;
		color: #006600; background-color: white;
		border: 3px groove; width: 100%; height: 400px; 
		overflow: auto;
	}
	.editbox-diff {
		font-family: <?php print $phpdocLangs[$lang]['font']; ?>;
		color: #006600; background-color: white;
		border: 3px groove; width: 100%; height: 550px; 
		overflow: auto;
	}
	a:link    { 
		text-decoration: none;
		color: #000066;
	}

	a:hover   { 
		text-decoration: none;
		color: #ff0000;
	}

	a:active  { 
		text-decoration: none;
		color: #ff0000;
	}

	a:visited { 
		text-decoration: none;
		color: #000066;
	}

</style>

<script language=javascript>

// Highlight input on mouse over
function irn(i) {
	var ob = document.getElementById('textAt['+i+']');
	ob.style.backgroundColor = '#EEEEEE';
}

// un-Highlight input on mouse over
function iro(i) {
	var ob = document.getElementById('textAt['+i+']');
	ob.style.backgroundColor = '#FFFFFF';	
}

var updated = false;

// Switch between fields when using arrow keys and translate selection by PgdDown key
function kp(i, e) {
	var ev= (window.event)? window.event: e;
	if(!ev || !ev.type) return false;
	var k,w;
	if (ev.keyCode) w=1;
	if (ev.charCode) w=2;
	if (ev.which) w=3;
	k = (ev.keyCode)?ev.keyCode: ((ev.charCode)? ev.charCode: ev.which);

	if (k==34) {  // PageDown to translate
		dblclk(document.getElementById('textAt['+i+']'));
		if (w==1) e.keyCode = 0;
		if (w==2) e.charCode = 0;
		if (w==3) e.which = 0;
		return false;
	}

	if (k==40 || k==38) {
		if (w==1) e.keyCode = 9;
		if (w==2) e.charCode = 9;
		if (w==3) e.which = 9;
	}
	return true;
}

// Update input/textarea size when editing
function ku(i, e) {
	updated = true;
	var ob = document.getElementById('textAt['+i+']');
	var l = ob.value.length;
	if (l) {
		ob.size = l;
		ob.cols = l;
	}	
}

// Translate using google API on double click 
function dblclk(obj) {
	// translate(obj);
	var selection = new Selection(obj);
	var s = selection.create();
	if (s.end) {
		var ln = s.end - s.start;
		var word = obj.value.substr(s.start, ln);
		translate(obj, word, s.start, s.end);
	}	
}


// Right To Left and Left To Right 
function rtl(button) {
	if (button.value == 'RTL') {
		button.value = 'LTR';
		document.getElementById('editbox').dir = 'rtl';
	} else {
		button.value = 'RTL';
		document.getElementById('editbox').dir = 'ltr';		
	}
}

// Prevent Losing changes 
function uconfirm() {
	if (updated) {
		return confirm('This will cancel any unsaved updates, are you sure?');
	}
	return true;
}

// Show hidden frame
function showhframe() {
	document.getElementById('hframe').style.display = '';
}


//------------- The following are required for Google translation 

// Cross Browser selectionStart/selectionEnd
// Version 0.2
// Copyright (c) 2005-2007 KOSEKI Kengo
// 
// This script is distributed under the MIT licence.
// http://www.opensource.org/licenses/mit-license.php

function Selection(textareaElement) {
    this.element = textareaElement;
}

Selection.prototype.create = function() {
    if (document.selection != null && this.element.selectionStart == null) {
        return this._ieGetSelection();
    } else {
        return this._mozillaGetSelection();
    }
}

Selection.prototype._mozillaGetSelection = function() {
    return { 
        start: this.element.selectionStart, 
        end: this.element.selectionEnd 
    };
}

Selection.prototype._ieGetSelection = function() {
    this.element.focus();

    var range = document.selection.createRange();
    var bookmark = range.getBookmark();

    var contents = this.element.value;
    var originalContents = contents;
    var marker = this._createSelectionMarker();
    while(contents.indexOf(marker) != -1) {
        marker = this._createSelectionMarker();
    }

    var parent = range.parentElement();
    if (parent == null ) {
        return { start: 0, end: 0 };
    }
    range.text = marker + range.text + marker;
    contents = this.element.value;

    var result = {};
    result.start = contents.indexOf(marker);
    contents = contents.replace(marker, "");
    result.end = contents.indexOf(marker);

    this.element.value = originalContents;
    range.moveToBookmark(bookmark);
    range.select();

    return result;
}

Selection.prototype._createSelectionMarker = function() {
    return "##SELECTION_MARKER_" + Math.random() + "##";
}
//------------		
</script>


<script type="text/javascript" src="http://www.google.com/jsapi"></script><script type="text/javascript">
//-- Google Translation API	      
		google.load("language", "1");
		
	var transTarget = new Array();
function translate(obj, word, s, e) {

	if (!word) return;
	if (obj.name!='xmldata') {
		obj.style.backgroundColor = '#22EE99';
	}
	transTarget[0] = obj;
	transTarget[1] = word;
	transTarget[2] = s;
	transTarget[3] = e;

	google.language.translate(word, "en", "<?php print $phpdocLangs[$lang]['id']; ?>", function(result) {  if (!result.error) { 
		//var container = document.getElementById("translation");   
		var w = transTarget[0].value;
		var w1 = '';
		if (transTarget[2]) {
			w1 = w.substr(0, transTarget[2]);
		}
		var w2 = w.substr(transTarget[2]+word.length);
		if (word.substr(word.length-1)==' ') w2 = ' '+w2;
		if (word.substr(0, 1)==' ') w1 = w1 + ' ';

		transTarget[0].value = w1+result.translation+w2;  
		var l = transTarget[0].value.length;
		if (l) {
			transTarget[0].size = l;
			transTarget[0].cols = l;
		}
		updated = true;
	}});
}

</script>


	File: <b><?php print $requestedFilename; ?></b><br>
	Source: <b><font color=white><?php
if ($source=='upath') print "User cached version";
if ($source=='apath') print "$lang Translated version";
if ($source=='epath') print "English version";

?></font></b><br>

<?php

if (!empty($requireLogin)) {
		print "Logged as: <b>$user[email]</b> <a href='login.php?and=logout' target=_top>logout</a><br>";
}

$url        = "editxml.php?file=$requestedFilename&source=$source";
$hideXML    = empty($_SESSION['hidexml']) ? '' : 'checked';
$splitFrames= empty($_SESSION['split'])   ? '' : 'checked';
$textEdit   = empty($_SESSION['textedit'])? '' : 'checked';
$lineByLine = empty($_SESSION['para'])    ? '' : 'checked';
?>
	<input type=checkbox name=hidexml value=1 <?php print $hideXML; ?> onClick="if (uconfirm()) document.location='<?php print $url; ?>&noframes=1&hidexml='+this.checked;" <?php if ($source=='diff') print 'disabled'; ?>  /> Hide XML Tags

	<input type=checkbox name=split value=1 <?php print $splitFrames; ?> onClick="if (uconfirm()) top.frames['fileframe'].location='<?php print $url; ?>&split='+this.checked;"> Split frames

	<input type=checkbox name=textedit value=1 <?php print $textEdit; ?> onClick="if (uconfirm()) document.location='<?php print $url; ?>&noframes=1&textedit='+this.checked;" <?php if ($source=='diff') print 'disabled'; ?> /> XML Text Editor

<input type=checkbox name=linebyline value=1 <?php print $lineByLine; ?> onClick="if (uconfirm()) document.location = '<?php print $url; ?>&noframes=1&par='+this.checked;" <?php if ($source=='diff') print 'disabled'; ?> /> Line by Line (no textarea)
<form action=editxml.php method=post target=hframe onsubmit="showhframe();">


<?php


$bs[0] = array('bcolor'=>'#CCCCCC', 'caption'=>'Latest English', 'link'=>"editxml.php?file=$requestedFilename&source=epath&noframes=1", 'tag'=>'epath', 'title'=>$status['lastEnRevision']);
$bs[1] = array('bcolor'=>'#CCCCCC', 'caption'=>$lang.' version', 'link'=>"editxml.php?file=$requestedFilename&source=apath&noframes=1", 'tag'=>'apath', 'title'=>$status['fileEnRevision']);

if ($requireLogin) {
	$bs[] = array('bcolor'=>'#CCCCCC', 'caption'=>'Cached version', 'link'=>"editxml.php?file=$requestedFilename&source=upath&noframes=1", 'tag'=>'upath');
}

if ($status['distance']>0) {
	$bs[] = array('bcolor'=>'#CCCCCC', 'caption'=>'Diff log '.$status['fileEnRevision'].' / '.$status['lastEnRevision'], 'link'=>"editxml.php?file=$requestedFilename&source=diff&noframes=1", 'tag'=>'diff');
}

foreach($bs as $i=>$b) {
	if ($b['tag']==$source) $b['bcolor'] = '#FFFFFF';
	print "<span style='background-color: $b[bcolor]; border: 1px solid black; border-bottom: 0px none white;'><a href='$b[link]' onclick='return uconfirm();' title='$b[title]'>$b[caption]</a>";
	print '</span>';
	if (($i+1)<count($bs)) print '|';
}

$direction = "LTR";
$directionx = "RTL";

if ($phpdocLangs[$lang]['direction']=='RTL' && ($source=='upath' || $source=='apath')) {
	$direction = "RTL";
	$directionx = "LTR";
}


if (empty($_SESSION['textedit']) || $source=='diff') {

?>

	<div id=editbox dir=<?php print $direction; ?> class="editbox<?php if ($source=='diff') print '-diff'; ?>">
<center><img id=loading src=images/loading.gif align=absmiddle></center>
		<pre><?php print $data; ?></pre>
</div>

<?php 
 } else {
	// Edit XML as Text
?>	
<center><img id=loading src=images/loading.gif align=absmiddle><div>
	<textarea name=xmldata id=editbox ondblclick="dblclk(this);" dir=<?php print $direction; ?> style="font-family: Fixedsys; background-color: white; width: 100%; height: 400px;"><?php print $data; ?></textarea></div>
</center>
<?php 
 }
 
if ($source!='diff') {

?>

<input name=noframes type=hidden value=1>
<input name=source type=hidden value="<?php print $source; ?>">
<input name=file type=hidden value="<?php print $requestedFilename; ?>">
<input type=button value=<?php print $directionx;?> onclick="rtl(this);">
<?php 
	if ($requireLogin) print '<input name=save type=submit value="Save in cache">';
?>
<input name=download type=submit value=Download> 

</form>

<!-- This frame is used to prevent losing changes if session has expired.. so login can be done inside -->
<iframe id=hframe name=hframe width=100% height=100 border=0 style="display: none"></iframe>

<ul>
 <li>After translating the file, please download (and save) it then either: 
  <ul>
   <li>Commit to CVS</li>
   <li>Or send it to the <?php print $phpdocLangs[$lang]['mailing']; ?> mailing list</li>
  </ul>
 </li>
 <li>If you have questions, contact the main discussion list (phpdoc@lists.php.net) or 
     write <?php print $phpdocLangs[$lang]['mailing']; ?> for translation specific concerns.
 </li>
 <li>In the future there will be a patch queue.</li>
</ul>
<?php 
}
?>

<script language=javascript>
document.getElementById('loading').style.display = 'none';
</script>


</body>
</html>
