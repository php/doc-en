<?php

/* 
 PLEASE DO NOT MAKE ANY MAJOR MODIFICATIONS TO THIS CODE!
 There is a new script collection on the way to replace
 these scripts. Please be patient while it will be ready
 to put here in CVS.
*/

include_once('common.php');
include_once('chm_settings.php');

if($LANGUAGE == "he") {
	include_once("./scripts/rtlpatch/HtmlParser.class.php");
	include_once("./scripts/rtlpatch/HtmlExtParser.class.php");
	
}

// This script takes much time to run
set_time_limit(0);

// Get ENV vars from the system
$original_index = "index.html";

// How many files were processed
$counter = 0;

// Open the directory, and do the work on all HTML files
$handle = opendir($HTML_PATH);
while (false !== ($filename = readdir($handle))) {
    if (strpos($filename, ".html") && ($filename != "fancy-index.html")) {
        if($LANGUAGE == "he") {
    		fancy_parser_design($filename);
        } else {
        	fancy_design($filename);
        }
    }
}
closedir($handle);

// Look for CHM index file (snap-downloader, cvs-usr with/without lang-support) 
if (false == ($content = oneLiner("make_chm_index_$LANGUAGE.html", true))) {
    if (false == ($content = oneLiner("$LANGUAGE/make_chm_index_$LANGUAGE.html", true))) {
        if (false == ($content = oneLiner("$HTML_PATH/../$LANGUAGE/make_chm_index_$LANGUAGE.html", true))) {
            $content = oneLiner("en/make_chm_index_en.html", true);
        }
    }
}

// Make GENTIME the actual date/time
$content = str_replace("[GENTIME]", date("D M d H:i:s Y"), $content);
$content = str_replace("[PUBTIME]", $publication_date, $content);
$content = setDocumentCharset($content, $LANGUAGES[$LANGUAGE]['mime_charset_name']);
$fp = fopen("$FANCY_PATH/fancy-index.html", "w");
fputs_wrapper($fp, $content);
fclose($fp);

copy("chm/make_chm_style.css", "$FANCY_PATH/style.css");
copy("chm/make_chm_spc.gif", "$FANCY_PATH/spacer.gif");

// Three files added (fancy-index.html, style.css and spacer.gif)
$counter += 3;
  
echo "\nConverting ready...\n";
echo "Total number of files written in $FANCY_PATH directory: $counter\n\n";
  
/***********************************************************************/
/* End of script lines, one main function follows                      */
/***********************************************************************/

// Convert one file from HTML => fancy HTML
function fancy_design($fname)
{
    global $HTML_PATH, $FANCY_PATH, $LANGUAGE, $LANGUAGES, $counter, $original_index, $publication_date;

    // Get the contents of the file from $HTML_PATH
    $content = oneLiner("$HTML_PATH/$fname", true);

    // CSS file linking
    $content = preg_replace("|</HEAD|", '<LINK REL="stylesheet" HREF="style.css"></HEAD', $content);

    // No margins around
    $content = preg_replace("/<BODY/", '<BODY TOPMARGIN="0" LEFTMARGIN="0"', $content);

    // HR dropout
    $content = preg_replace("/<HR\\s+ALIGN=\"LEFT\"\\s+WIDTH=\"100%\">/", '', $content);

    // Whole page table and backgrounds
    $wpbegin = '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3">';
    $bnavt = '<TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">';
    $lnavt = '<TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR>';
    $space = '<IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1">';

    // Navheader backgound
    $content = preg_replace("/<DIV\\s+CLASS=\"NAVHEADER\"\\s*><TABLE(.*)CELLPADDING=\"0\"(.*)<\\/TABLE\\s*><\\/DIV\\s*>/Us",
        $wpbegin . '<DIV CLASS="NAVHEADER">' . $bnavt . '<TR><TD><TABLE\\1CELLPADDING="3"\\2</TABLE></TD></TR>' . $lnavt . '</TABLE></DIV></TD></TR><TR><TD>' . $space . '</TD><TD HEIGHT="100%" VALIGN="TOP" WIDTH="100%"><BR>', $content);

    // Navfooter backgound
    $content = preg_replace("/<DIV\\s+CLASS=\"NAVFOOTER\"\\s*><TABLE(.*)CELLPADDING=\"0\"(.*)<\\/TABLE\\s*><\\/DIV\\s*>/Us",
        '<BR></TD><TD>' . $space . '</TD></TR><TR><TD COLSPAN="3"><DIV CLASS="NAVFOOTER">' . $bnavt . $lnavt . '<TR><TD><TABLE\\1CELLPADDING="3"\\2</TABLE></TD></TR></TABLE></DIV></TD></TR></TABLE>', $content);

    // Fix copyright page fault...
    if ($fname == "copyright.html") {
        $content = preg_replace("/&#38;copy;/", "&copy;", $content);
        $content = preg_replace("/<A\\s+HREF=\"$original_index#(authors|translators)\"/U", "<A HREF=\"fancy-index.html\"", $content);
        $content = preg_replace("|(</TH\\s*></TR\\s*>)|", "\\1<TR><TH COLSPAN=\"3\" ALIGN=\"center\">&nbsp;</TH></TR>", $content);
        $content = preg_replace("|(&nbsp;</TD\\s*></TR\\s*>)|", "\\1<TR><TD COLSPAN=\"3\" ALIGN=\"center\">&nbsp;</TD></TR>", $content);
    }

    // Fix the original manual index to look far better...
    elseif ($fname == "$original_index") {

        // Find out manual generation date
        if (preg_match('|<P\s+CLASS="pubdate"\s*>([\\d-]+)<BR></P\s*>|U', $content, $match)) {
            $publication_date = $match[1];
        } else { 
            $publication_date = 'n/a';
        }

        // Modify the index file to meet our needs
        preg_match('|CLASS=\"title\"\\s*><A\\s+NAME=\"manual\"\\s*>(.*)</A\\s*>(.*)</H1|U', $content, $match);
        $indexchange = '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3"><DIV CLASS="NAVHEADER"><TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%"><TR><TD><TABLE
        WIDTH="100%" BORDER="0" CELLPADDING="3" CELLSPACING="0"><TR><TH COLSPAN="3">'.$match[2].'</TH></TR><TR><TD COLSPAN="3" ALIGN="center">&nbsp;</TD></TR></TABLE></TD></TR><TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR></TABLE>
        </DIV></TD></TR><TR><TD><IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1"></TD><TD HEIGHT="100%" VALIGN="TOP" WIDTH="100%"><BR>';
        $content = preg_replace("/(<DIV\\s+CLASS=\"BOOK\")/", "$indexchange\\1", $content);
        $content = preg_replace("/(<DIV\\s+CLASS=\"author\").*<HR>/Us", "", $content);
        preg_match('|<DIV\\s+CLASS="TOC"\\s*><DL\\s*><DT\\s*><B\\s*>(.*)</B\\s*>|U', $content, $match);
        $content = preg_replace("|(CLASS=\"title\"\\s+><A\\s+NAME=\"manual\"\\s*>).*(</A\\s*>).*(</H1)|U", "\\1$match[1]\\2\\3", $content);
        $content = preg_replace("|<DT\\s*><B\\s*>(.*)</B\\s*></DT\\s*>|U", "", $content);

    }

    // Print out that new file to $FANCY_PATH
    $fp = fopen("$FANCY_PATH/$fname", "w");
    $content = setDocumentCharset($content, $LANGUAGES[$LANGUAGE]['mime_charset_name']);
    fputs_wrapper($fp, $content);
    fclose($fp);

    // Print out a message to see the progress
    echo "$FANCY_PATH/$fname ready...\n";
    $counter++;
    
} // fancy_design() function end


// Convert one file from HTML => fancy HTML using CHtmlParser
function fancy_parser_design($fname)
{
    global $HTML_PATH, $FANCY_PATH, $LANGUAGE, $LANGUAGES, $counter, $original_index, $publication_date;
	
    global $EHType,$HEType;
    
    // Get the contents of the file from $HTML_PATH
    //TODO: iconv stuff, for when charset element > byte
    $content = file_get_contents("$HTML_PATH/$fname");
	$tree = new CHtmlExtParse($content);
	
	// CSS file linking
	$head = $HEType["head"];
	if(isset($tree->EBT[$head][0]))
		$tree->ATE[$tree->EBT[$head][0]]["chaintoend"] = '<LINK REL="stylesheet" HREF="style.css">';
	
	// Charset:
	$meta = $HEType["meta"];
	if(isset($tree->EBT[$meta])){
		for($a=0;$a<count($tree->EBT[$meta]);$a++){
			$elem = &$tree->ATE[$tree->EBT[$meta][$a]];
			if(isset($elem["http-equiv"]) && $elem["http-equiv"]=="Content-type")
				$elem["content"] = "text/html; {$LANGUAGES[$LANGUAGE]['mime_charset_name']}";
		}
	}
	
    // No margins around
    $body = $HEType["body"];
	if(isset($tree->EBT[$body][0])){
		$tree->ATE[$tree->EBT[$body][0]]["TOPMARGIN"] ="0";
		$tree->ATE[$tree->EBT[$body][0]]["LEFTMARGIN"] ="0";
	}
	
	
    // HR dropout
    $tmp=0;
	do{
		if($tmp = $tree->get_element_id_by_rule(array("tag"=>"hr","properties"=>array("align","LEFT","width","100%"),"offset"=>($tmp+1)))){
			$tree->change_tag_type($tmp,__HTML_FREE_ENGLISH__);
		}
	} while($tmp);
	
    // Whole page table and backgrounds
    $wpbegin = '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3">';
    $bnavt = '<TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">';
    $lnavt = '<TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR>';
    $space = '<IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1">';

    // Navheader backgound
    if($tmp = $tree->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","NAVHEADER")))){
		$tree->ATE[$tmp]["data"] = "$wpbegin<DIV CLASS=\"NAVHEADER\">$bnavt<TR><TD>";
		$tree->ATE[$tree->ECE[$tmp]]["data"] = "</TD></TR>$lnavt</TABLE></DIV></TD></TR><TR><TD>$space</TD><TD HEIGHT=\"100%\" VALIGN=\"TOP\" WIDTH=\"100%\"><BR>";
		$tree->ATE[$tmp+1]["cellpadding"] = "3";
		$tree->change_tag_type($tmp,__HTML_FREE_ENGLISH__);
	}
    
	// Navfooter backgound
    if($tmp = $tree->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","NAVFOOTER")))){
		$tree->ATE[$tmp]["data"] = "<BR></TD><TD>$space</TD></TR><TR><TD COLSPAN=\"3\"><DIV CLASS=\"NAVFOOTER\">{$bnavt}{$lnavt}<TR><TD>";
		$tree->ATE[$tree->ECE[$tmp]]["data"] = "</TD></TR></TABLE></DIV></TD></TR></TABLE>";
		$tree->ATE[$tmp+1]["cellpadding"] = "3";
		$tree->change_tag_type($tmp,__HTML_FREE_ENGLISH__);
	}
	
    // Fix copyright page fault...
    if ($fname == "copyright.html") {
    	// it just looks that no more need to fix the copyright.
    }

    // Fix the original manual index to look far better...
    elseif ($fname == "$original_index") {
		// Find out manual generation date
    	if($tmp = $tree->get_element_id_by_rule(array("tag"=>"p","properties"=>array("class","pubdate")))){
			$publication_date = $tree->ATE[$tmp+1]["data"];
		} else {
            $publication_date = 'n/a';
        }
		
        
         // Modify the index file to meet our needs
        $tmp = $tree->get_element_id_by_rule(array("tag"=>"h1","properties"=>array("class","title")));
		$tit = isset($tree->ATE[$tmp+2]["data"])?$tree->ATE[$tmp+2]["data"]:"";
		$tit2 ="";
       
        $tmp = $tree->get_element_id_by_rule(array("tag"=>"div","properties"=>array("class","BOOK")));
        $tree->ATE[$tmp-1]["chaintoend"] = 
        '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3"><DIV CLASS="NAVHEADER"><TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%"><TR><TD><TABLE
        WIDTH="100%" BORDER="0" CELLPADDING="3" CELLSPACING="0"><TR><TH COLSPAN="3">'.$tit2.'</TH></TR><TR><TD COLSPAN="3" ALIGN="center">&nbsp;</TD></TR></TABLE></TD></TR><TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR></TABLE>
        </DIV></TD></TR><TR><TD><IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1"></TD><TD HEIGHT="100%" VALIGN="TOP" WIDTH="100%"><BR>';
        
        /** TODO complete this:
        $content = preg_replace("/(<DIV\\s+CLASS=\"author\").*<HR>/Us", "", $content);
        preg_match('|<DIV\\s+CLASS="TOC"\\s*><DL\\s*><DT\\s*><B\\s*>(.*)</B\\s*>|U', $content, $match);
        $content = preg_replace("|(CLASS=\"title\"\\s+><A\\s+NAME=\"manual\"\\s*>).*(</A\\s*>).*(</H1)|U", "\\1$match[1]\\2\\3", $content);
        $content = preg_replace("|<DT\\s*><B\\s*>(.*)</B\\s*></DT\\s*>|U", "", $content);
        /**/
    }
	
    // Print out that new file to $FANCY_PATH
    $fp = fopen("$FANCY_PATH/$fname", "w");
  	
    $content = $tree->get();
    $tree->unsetme();
    //TODO: iconv stuff, for when charset element > byte
    fputs($fp, $content);
    fclose($fp);

    // Print out a message to see the progress
    echo "$FANCY_PATH/$fname ready...\n";
    $counter++;
} // fancy_parser_design() function end

?>
