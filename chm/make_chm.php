<?php

/* 
 PLEASE DO NOT MAKE ANY MAJOR MODIFICATIONS TO THIS CODE!
 There is a new script collection on the way to replace
 these scripts. This script is updated to handle many
 things. Code backported from the new script collection.
 Please be patient while it will be ready to put here.
 See make_chm.README for information until then.
*/

// Used directories and files
$HTML_PATH     = getenv("PHP_HELP_COMPILE_DIR");
$FANCY_PATH    = getenv("PHP_HELP_COMPILE_FANCYDIR");
$LANGUAGE      = getenv("PHP_HELP_COMPILE_LANG");
$INDEX_IN_HTML = "index.html";
$INTERNAL_CHARSET = "UTF-8";
$DEFAULT_FONT = "Arial,10,0";

if (empty($FANCY_PATH)) { $FANCY_PATH = $HTML_PATH; }

// Array to manual code -> HTML Help Code conversion
// Code list: http://www.helpware.net/htmlhelp/hh_info.htm
$LANGUAGES = array(
    "tw"    => array(
                   "langcode" => "0x404 Traditional Chinese",
                   "preferred_charset" => "CP950",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "cs"    => array(
                   "langcode" => "0x405 Czech",
                   "preferred_charset" => "Windows-1250",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "de"    => array(
                   "langcode" => "0x407 German (Germany)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "en"    => array(
                   "langcode" => "0x809 English (United Kingdom)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "es"    => array(
                   "langcode" => "0xc0a Spanish (International Sort)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "fr"    => array(
                   "langcode" => "0x40c French (France)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "fi"    => array(
                   "langcode" => "0x40b Finnish",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "hu"    => array(
                   "langcode" => "0x40e Hungarian",
                   "preferred_charset" => "Windows-1250",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "it"    => array(
                   "langcode" => "0x410 Italian (Italy)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "ja"    => array(
                   "langcode" => "0x411 Japanese",
                   "preferred_charset" => "CP932",
                   "preferred_font" => "MS P Gothic,10,0"
               ),
    "kr"    => array(
                   "langcode" => "0x412 Korean",
                   "preferred_charset" => "CP949",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "nl"    => array(
                   "langcode" => "0x413 Dutch (Netherlands)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "pt_BR" => array(
                   "langcode" => "0x416 Portuguese (Brazil)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "sl"    => array(
                   "langcode" => "0x41d Slovenian",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "sv"    => array(
                   "langcode" => "0x41d Swedish (Netherlands)",
                   "preferred_charset" => "Windows-1252",
                   "preferred_font" => $DEFAULT_FONT
               ),
    "zh"    => array(
                   "langcode" => "0x804 Simplified Chinese",
                   "preferred_charset" => "CP936",
                   "preferred_font" => $DEFAULT_FONT
               )
);

// Files on the top level of the TOC
$MAIN_FILES = array(
    "getting-started.html",
    "langref.html",
    "features.html",
    "funcref.html",
    "zend.html",
    "faq.html",
    "appendixes.html"
);

// backwards compatibility
if (!function_exists("file_get_contents")) {
    function file_get_contents($file)
    {
        $cnt = file($file);
        if ($cnt !== false) {
            return join('', $cnt);
        }
        return false;
    }
}

// Header for index and toc 
$HEADER = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html>
<head>
  <meta name="generator" content="PHP 4 - Auto TOC script">
  <!-- Sitemap 1.0 -->
</head>
<body>
  <object typre="text/site properties">
    <param name="Window Styles" value="0x800227">
  </object>
  <ul>';

makeProjectFile();
makeContentFiles();

// Generate the HTML Help content files 
function makeContentFiles()
{
    global $LANGUAGE, $MANUAL_TITLE, $HEADER, $MAIN_FILES,
           $HTML_PATH, $INDEX_IN_HTML, $FIRST_PAGE;

    $toc   = fopen("php_manual_$LANGUAGE.hhc", "w");
    $index = fopen("php_manual_$LANGUAGE.hhk", "w");

    // Write out file headers
    fputs_wrapper($toc,   $HEADER);
    fputs_wrapper($index, $HEADER);

    // Read original index file and drop out newlines
    $indexline = oneLiner("$HTML_PATH/$INDEX_IN_HTML");

    // Print out the objects, autoparsing won't find
    mapAndIndex($MANUAL_TITLE, $FIRST_PAGE, "    ", $toc, $index, 21);

    // There is a fancy index
    if ($FIRST_PAGE != $INDEX_IN_HTML) {

        // Find the name of the Table of Contents
        preg_match('|CLASS=\"TOC\" *><DL *><DT *><B *>(.*)</B|U', $indexline, $match);
        if (empty($match[1])) { // Fallback
            $match[1] = "Table of Contents";
        }
        mapAndIndex($match[1], $INDEX_IN_HTML, "    ", $toc, $index, 21);

    }

    // Find the name of the Preface
    preg_match('|<A +HREF="preface.html" *>([^<]*)</A *>|U', $indexline, $match);
    if (empty($match[1])) { // Fallback
        $match[1] = "Preface";
    }
    mapAndIndex($match[1], "preface.html", "    ", $toc, $index);

    // Now autofind the main pages

    $MAIN_REGEXP = join("|", $MAIN_FILES);

    preg_match_all("![IVX]+[^<]*<A\\s+HREF=\"($MAIN_REGEXP)\"\\s*>([^<]+)</A\\s*>(.+)</DT\\s*></DL\\s*></DD\\s*><DT\\s*>!Ui", $indexline, $matches, PREG_SET_ORDER);
    
    // Go through the main files, and link in subpages
    foreach ($matches as $matchinfo) {
        mapAndIndex($matchinfo[2], $matchinfo[1], "    ", $toc, $index);

        fputs_wrapper($toc, "\n      <ul>\n");
        preg_match_all("!<A\\s+HREF=\"([^\"]+)\"\\s*>([^<]*)</A\\s*>!iU", $matchinfo[3], $subpages, PREG_SET_ORDER);

        foreach ($subpages as $spinfo) {
            mapAndIndex($spinfo[2], $spinfo[1], "        ", $toc, $index);
            findDeeperLinks($spinfo[1], $toc, $index);
        }
        fputs_wrapper($toc, "\n      </ul>\n");
    }

    // Link in directly the copyright page
    $copyline = oneLiner("$HTML_PATH/copyright.html");
    preg_match('|<A\\s+NAME="copyright"\\s*></A\\s*><P\\s*><B\\s*>([^<]*)</B|U', $copyline, $match);
    if (empty($match[1])) { // Fallback
        $match[1] = "Copyright";
    }
    mapAndIndex($match[1], "copyright.html", "    ", $toc, $index, 17);

    // Write out closing line, and end files
    fputs_wrapper($index, "  </ul>\n</body>\n</html>");
    fputs_wrapper($toc,   "  </ul>\n</body>\n</html>");
    fclose($index);
    fclose($toc);
} // makeContentfiles() function end

// Generates the HTML Help project file
function makeProjectFile()
{
    global $LANGUAGE, $MANUAL_TITLE, $LANGUAGES,
           $HTML_PATH, $FANCY_PATH, $INDEX_IN_HTML,
           $FIRST_PAGE;

    // Try to find the fancy index file
    if (file_exists("$FANCY_PATH/fancy-index.html")) {
        $FIRST_PAGE = "fancy-index.html";
    } else {
        $FIRST_PAGE = $INDEX_IN_HTML;
    }
           
    // Start writing the project file
    $project = fopen("php_manual_$LANGUAGE.hhp", "w");
    fputs_wrapper($project, "[OPTIONS]\n");
    fputs_wrapper($project, "Compatibility=1.1 or later\n");
    fputs_wrapper($project, "Compiled file=php_manual_$LANGUAGE.chm\n");
    fputs_wrapper($project, "Contents file=php_manual_$LANGUAGE.hhc\n");
    fputs_wrapper($project, "Index file=php_manual_$LANGUAGE.hhk\n");
    fputs_wrapper($project, "Default Font={$LANGUAGES[$LANGUAGE]['preferred_font']}\n");
    fputs_wrapper($project, "Default Window=phpdoc\n");
    fputs_wrapper($project, "Default topic=$FANCY_PATH\\$FIRST_PAGE\n");
    fputs_wrapper($project, "Display compile progress=Yes\n");
    fputs_wrapper($project, "Full-text search=Yes\n");

    // Get the proper language code from the array
    fputs_wrapper($project, "Language={$LANGUAGES[$LANGUAGE]["langcode"]}\n");

    // Now try to find out how the manual named in the actual language
    // this must be in the index.html file as the title (DSSSL generated)
    $content = oneLiner("$HTML_PATH/$INDEX_IN_HTML");
    if (preg_match("|<TITLE\s*>([^<]*)</TITLE\s*>|U", $content, $found)) {
        $MANUAL_TITLE = $found[1];
    } else { // Fallback
        $MANUAL_TITLE = "PHP Manual";
    }

    fputs_wrapper($project, "Title=$MANUAL_TITLE\n");

    // Define the phpdoc window style (adds more functionality)
    fputs_wrapper($project, "\n[WINDOWS]\nphpdoc=\"$MANUAL_TITLE\",\"php_manual_$LANGUAGE.hhc\",\"php_manual_$LANGUAGE.hhk\"," .
          "\"$FANCY_PATH\\$FIRST_PAGE\",\"$FANCY_PATH\\$FIRST_PAGE\",,,,,0x23520,,0x386e,,,,,,,,0\n");

    // Write out all the filenames as in FANCY_PATH
    fputs_wrapper($project, "\n[FILES]\n");
    $handle = opendir($FANCY_PATH);
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            fputs_wrapper($project, "$FANCY_PATH\\$file\n");
        }
    }
    closedir($handle);
    fclose($project);
} // makeProjectFile() function end

// Print out a SiteMap object for a file
function mapAndIndex($name, $local, $tabs, $toc, $index, $imgnum = "auto")
{
    global $FANCY_PATH;
    $name = str_replace('"', '&quot;', $name);

    fputs_wrapper($toc, "
$tabs<li><object type=\"text/sitemap\">
$tabs  <param name=\"Name\" value=\"$name\">
$tabs  <param name=\"Local\" value=\"$FANCY_PATH\\$local\">
");

    if ($imgnum != "auto") {
        fputs_wrapper($toc, "$tabs  <param name=\"ImageNumber\" value=\"$imgnum\">\n");
    }
    fputs_wrapper($toc, "$tabs  </object>\n");

    fputs_wrapper($index, "
    <li><object type=\"text/sitemap\">
      <param name=\"Local\" value=\"$FANCY_PATH\\$local\">
      <param name=\"Name\" value=\"$name\">
    </object></li>
");

} // mapAndIndex() function end


// Process a file, and find any links need to be presented in tree
function findDeeperLinks ($filename, $toc, $index)
{
    global $HTML_PATH;
    $contents = oneLiner("$HTML_PATH/$filename");
    
    // Find all sublinks
    if (preg_match_all("!<DT\\s*><A\\s+HREF=\"(([\\w\\.-]+\\.)+html)(\\#[\\w\\.-]+)?\"\\s*>([^<]*)</A\\s*>!U", $contents, $matches, PREG_SET_ORDER)) {
        
        // Print out the file informations for all the links
        fputs_wrapper($toc, "\n        <ul>");
        foreach ($matches as $onematch) {
            $param["html"] = $onematch[1];
            if (!empty($onematch[3])) {
                $param["html"] .= $onematch[3];
            }
            $param["title"] = strip_tags($onematch[4]);
            mapAndIndex($param["title"], $param["html"], "          ", $toc, $index);
        }
        fputs_wrapper($toc, "        </ul>\n");

    } else {

        // Uncomment this if you want to debug the above pregs
        // Note: there are many files normally without deeper
        // TOC info, eg. language.expressions.html

        // echo "no deeper TOC info found in $filename\n";
        // return;

    }
    
} // findDeeperLinks() function end

function fputs_wrapper($fp, $str)
{
    fputs($fp, convertCharset($str));
}

// Return a file joined on one line
function oneLiner($filename)
{
    global $INTERNAL_CHARSET;

    $buf = preg_replace("/[\r|\n]{1,2}/U", " ", file_get_contents($filename));
    $charset = detectDocumentCharset($buf);

    if ($charset === false) $charset = "UTF-8";

    if ($charset != $INTERNAL_CHARSET) {
        if (function_exists("iconv")) {
            $buf = iconv($charset, $INTERNAL_CHARSET, $buf);
        } elseif (function_exists("mb_convert_encoding")) {
            $buf = mb_convert_encoding($buf, $INTERNAL_CHARSET, $charset);
        } elseif (preg_match("/^UTF-?8$/i", $INTERNAL_CHARSET) && preg_match("/^(ISO-8859-1|WINDOWS-1252)$/i", $charset)) {
            $buf = utf8_encode($buf);
        } else {
            die("charset conversion function is not available.");
        }
    }
    return $buf;
}

function convertCharset($buf)
{
    global $LANGUAGE, $LANGUAGES, $INTERNAL_CHARSET;

    $charset = $LANGUAGES[$LANGUAGE]['preferred_charset'];

    if ($charset != $INTERNAL_CHARSET) {
        if (function_exists("iconv")) {
            $buf = iconv($INTERNAL_CHARSET, $charset, $buf);
        } elseif (function_exists("mb_convert_encoding")) {
            $buf = mb_convert_encoding($buf, $charset, $INTERNAL_CHARSET);
        } elseif (preg_match("/^UTF-?8$/i", $INTERNAL_CHARSET) && preg_match("/^(ISO-8859-1|WINDOWS-1252)$/i", $charset)) {
            $buf = utf8_decode($buf);
        } else {
            die("$LANGUAGE locale is not supported.");
        }
    }
    return $buf;
} // oneLiner() function end

// Returns the name of character set in the given document
function detectDocumentCharset($doc)
{
    if (preg_match("/<META\\s+HTTP-EQUIV=\"CONTENT-TYPE\"\\s+CONTENT=\"TEXT\\/HTML;\\s+CHARSET=([\\w\\d-]*)\"\\s*>/iU", $doc, $reg)) {
        return $reg[1];
    }
    return false;
}
?>
