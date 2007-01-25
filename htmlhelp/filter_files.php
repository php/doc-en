<?php // $Id$

/* 
   This file is part of the Windows Compiled HTML Help
   Manual Generator of the PHP Documentation project.
   
   The filters included in this file are to refine
   the XSL generated HTML codes. Some filters may
   be converted to XSL templates, but not all.
*/

if (!isset($HTML_SRC)) {
    die("This script is called from make_chm.php to filter output from XSL DocBook templates");
}

// make sure our regexes work under PHP >= 5.1
ini_set('pcre.backtrack_limit', PHP_INT_MAX);

$counter = filterFiles();

// Filter XSL generated files through some refine filters
function filterFiles()
{
    global $HTML_SRC, $HTML_TARGET, $INDEX_FILE, $LANGUAGE;
    
    // How many files were processed
    $counter = 0;
    
    // Try to figure out what index file to use
    if (file_exists("$HTML_SRC/index.html")) {
        $INDEX_FILE = "index.html";
    } else { $INDEX_FILE = "manual.html"; }

    // Open the directory, and do the work on all HTML files
    $handle = opendir($HTML_SRC);
    while (false !== ($filename = readdir($handle))) {
        // Only process html files
        if (strpos($filename, ".html")) {
            $counter++;
            echo "\r                                                                       \r";
            echo "> $counter $filename";
            refineFile($filename);
        }
    }
    closedir($handle);

    // Copy all the images to the target directory
    exec("mkdir $HTML_TARGET\\figures");
    exec("copy $HTML_SRC\\figures $HTML_TARGET\\figures /Y");

    // Copy all supplemental files to the target directory
    exec("copy suppfiles\\html $HTML_TARGET /Y");
    
    // Copy all HTML Help files to the target directory too
    exec("copy $HTML_SRC\\php_manual_$LANGUAGE.hh? $HTML_TARGET /Y");
    
    // Rewrite script file to include current language and date
    $script_js = join("", file("$HTML_TARGET/_script.js"));
    $script_js = str_replace("LANGUAGE_HERE", $LANGUAGE, $script_js);
    $script_js = str_replace("DATE_HERE", date("Y-m-d"), $script_js);
    $fp = fopen("$HTML_TARGET/_script.js", "w");
    fwrite($fp, $script_js);
    fclose($fp);
   
    // Rewrite HHP file to make UK English default language like in template
    // prior to DocBook XSL 1.66.1. Add supplementary files to [FILES] section
    // and also insert [MERGE FILES] section
    $hhp_file = "$HTML_TARGET/php_manual_$LANGUAGE.hhp";

    if (file_exists($hhp_file)) {
        $php_hhp = join("", file($hhp_file));

        // Get rid of hh autoindex "feature" and set UK English language
        $php_hhp = preg_replace("|Auto Index=Yes\W+|i","",$php_hhp);
        $php_hhp = str_replace("Language=0x0409 English (UNITED STATES)","Language=0x0809 English (UNITED KINGDOM)",$php_hhp);

        // Capturing line delimiter
        preg_match("|\[FILES\](\W+)\w|i",$php_hhp,$matches);
        $delim = $matches[1];

        // Building list of supplemental files. glob doesn't work in 4.3.7/8 :(
        $d = dir("suppfiles/html");
        $supp_files = "";
        while (false !== ($entry = $d->read())) {
            if ($entry != "." && $entry != ".." && !is_dir($entry))
                $supp_files .= $delim.$entry;
        }
        $supp_files .= $delim."_index.html";

	// Build list of figures
	$d = dir("$HTML_TARGET/figures");
	$figure_files = "";
        while (false !== ($entry = $d->read())) {
            if ($entry != "." && $entry != ".." && !is_dir($entry))
                $figure_files .= $delim.'figures\\'.$entry;
        }

        // Insert [MERGE] section, figures and supplemental files 
        $php_hhp = preg_replace(
           "|\[FILES\]((\W+)\w)|i",
           "[MERGE FILES]$2php_manual_notes.chm$2$2[FILES]$figure_files$supp_files$1",
           $php_hhp);
        $fp = fopen($hhp_file, "w");
        fwrite($fp, $php_hhp);
        fclose($fp);
    }

    return $counter;
} // filterFiles() function end

// Refine HTML code in XSL generated files
function refineFile($filename)
{
    global $HTML_SRC, $HTML_TARGET, $INDEX_FILE, $preNum;
    
    // The number of <pre> parts is zero (used for example copy links)
    $preNum = 0;
    
    // Read in the contents of the source file
    $content = join("", file("$HTML_SRC/$filename"));
    
    //------------------------------------------------------------------
    // Find page title and format it properly
    preg_match('!<title>\s*(.+)</title>!Us', $content, $matched);
    $page_title = $matched[1];

    // Replace title with simple <title> content [shorter, without tags]
    $content = preg_replace(
        '!<h(\d)[^>]*>.+</h\1>!Us',
        "<h1 class=\"masterheader\"><span id=\"pageTitle\">$page_title</span></h1>",
        $content,
        1
    );
    
    //------------------------------------------------------------------
    // Additional divisions for skin support

    // Adding div id="pageHeaders" instead of titlepage div
    $content = preg_replace('|<div class="titlepage">|', '<div id="pageHeaders">', $content, 1);
    
    // For headers we have several possibilities how to close div id="pageHeaders"
    // and open div with id="pageText"
    if (strpos($content, '<div class="refnamediv">') !== FALSE) {
        
        // A function page
        
        // extend pageHeaders div (former titlepage) to cover refnamediv with funcAvail, 
        // funcUsage and funcPurpose spans
        $content = str_replace('</h1></div><div class="refnamediv">', '</h1>', $content);

        // insert pageText div before first text division like refsect1, sect1 and so on
        // i.e. just after former titlepage end
        $content = preg_replace(
            '!(</h2></div>)(<div class="([^"]+)")!i',
            '\1<div id="pageText">\2',
            $content,
            1
        );
    }

    // The index page
    elseif ($filename == $INDEX_FILE)  {
        
        // Need to close one more div on this page before adding pageHeader end and pageText start
        $content = str_replace(
            "</h1></div>",
            '</h1></div></div></div><div id="pageText"><div>',
            $content
        );

        $content = str_replace("<hr></div>","</div><hr>", $content);
    }
    
    // Normal page
    else {
        
        // Remove empty wrapping divs for pageHeaders
        $content = preg_replace(
            '!<div id="pageHeaders">((<div>)+)(<h1.+?</h1>)((</div>)+)!is',
            '<div id="pageHeaders">\3</div>',
            $content,
            1
        );

        // Insert pageText like in function page
        $content = preg_replace(
            '!(</h1></div>)(<div class="([^"]+)"|<p>)!is',
            '\1<div id="pageText">\2',
            $content,
            1
        );
    }

    // Instead of closing pageText div right before div with id="pageNotes" we delete start
    // tag of top level div with class="refentry", "sect1" or any other chunkable class name.
    // This div ends just where we need our pageText to end and is not overlapped by pageHeaders
    $content = preg_replace(
        '!<div class="[^"]+" lang="[^"]+">!i', "", $content, 1
    );
    
    // If this is the index file, correct it
    if ($filename == $INDEX_FILE) {
       $content = newIndex($content);
    }
    
    //------------------------------------------------------------------
    // Change pre sections look (examples, screen outputs, etc).
    $content = preg_replace_callback(
        '!<pre class="([^"]+)">(.+)</pre>!Us',
        "formatPre",
        $content
    );

    //------------------------------------------------------------------
    // Add .datatable class for tables to ease styling
    $content = preg_replace('!<div class="(informal)?table".*<table !U',
                              '\\0class="datatable" ',$content);

    //------------------------------------------------------------------
    // Put <p> tags after all </ul> or </div> or </table> close tags to
    // enable CSS support for those paragraphs (these break a <p>)
    // BUT do not put a P after our special notes container
    $content = preg_replace('!</(ul|div|table)>!Us', '</\\1><p>', $content);
    $content = str_replace('<div id="pageNotes"></div><p>', '<div id="pageNotes"></div>', $content);
    
    //------------------------------------------------------------------
    // Delete duplicate <p> tags from code, unneded <p></p> parts, and
    // <p> before <table> or <div> or </div> or </body> or <ul>
    $content = preg_replace('!<p>\s*<p>!Us', '<p>', $content);
    $content = preg_replace('!<p>\s*</p>!Us', '', $content);
    $content = preg_replace('!<p>\s*<(table|div|/div|/body|ul)!Us', '<\\1', $content);

    //------------------------------------------------------------------
    // Drop out all the <div> and </div> tags left (no need to have them)
    //$content = preg_replace('!</?div[^>]*>!Us', '', $content);

    // !!! Temporary fix for XSLT output escaping problems
    $content = preg_replace("!&amp;raquo; !", "&raquo; ", $content);
    $content = preg_replace("!&amp;nbsp; !", "&nbsp; ", $content);
    
    //------------------------------------------------------------------
    // Write out file to HTML output directory
    $fp = fopen("$HTML_TARGET/$filename", "w");
    fwrite($fp, $content);
    fclose($fp);
    
} // newFace() function end


// Make the old index look somewhat better
function newIndex ($content)
{
    global $HTML_TARGET;
    
    // Get contents we need to build the _index.html file
    preg_match("!^(.+)<hr>!s", $content, $_index1);
    preg_match("!</div></div>(<a id=\"user_notes\">.+</html>)!s", $content, $_index2);
    
    // Write out the two components to form a complete file
    $fp = fopen("$HTML_TARGET/_index.html", "w");
    fwrite($fp, $_index1[1] . $_index2[1]);
    fclose($fp);
    
    // Drop out authors list (this is on the frontpage)
    $content = preg_replace(
        '!<div id="pageText"><div>.*<hr>!Us',
        '<div id="pageText">',
        $content
    );
    // Get TOC title from HTML code
    preg_match(
        '!<div class="toc"><p><b>(.+)</b></p>!U',
        $content,
        $match
    );
    // Put toc title into title places
    $content = preg_replace(
        '!<title>(.+)</title>!U',
        "<title>$match[1]</title>",
        $content
    );
    $content = preg_replace(
        '!<span id="pageTitle">(.+)</span>!U',
        "<span id=\"pageTitle\">$match[1]</span>",
        $content
    );
    // Drop out small TOC title
    $content = preg_replace(
        '!<div class="toc"><p><b>(.+)</b></p>!U', 
        '<div class="toc">',
        $content
    );
    return $content;

} // newIndex() function end

// Change pre sections look
function formatPre ()
{
    // Number of <pre> sections on this page
    global $preNum;
    
    // Construct clipboard copy link
    $preNum++;
    $linkwdiv = '<div class="codelink"><a href="javascript:void(0);" onclick="copyExample(\''
            . $preNum . '\')">copy to clipboard</a></div><div class="examplecode">';
    
    // Replace all hard line breaks
    list($pre_found) = func_get_args();
    
    // Not a PHP example
    if ($pre_found[1] != 'php') {
        return $linkwdiv . '<code id="example_' . $preNum . '">' . pre2code(trim($pre_found[2])) . '</code></div><p>';
    }
        
    // Convert entities to characters for color coding
    $example = str_replace(
        array("&gt;", "&lt;", "&amp;", "&quot;"),
        array(">", "<", "&", "\""),
        trim($pre_found[2])
    );

    if (!strstr($example, "<?php")) {
        $example = "<?php " . $example . " ?>";
        $delimiter = FALSE;
    } else {
        $delimiter = TRUE;
    }

    // Get highlited source code
    $colored_example = highlight_string($example, true);
    
    // Strip out PHP delmiter, if we added it
    if (!$delimiter) {
        $colored_example = str_replace(
            array (
               '<font color="#0000CC">&lt;?php&nbsp;</font>',
               '&lt;?php&nbsp;',
               '<font color="#0000CC">?&gt;</font>'
            ),
            array ('', '', ''),
            $colored_example
        );
    }
    
    // Get much smaller source code by converting
    // display identical things to smaller size
    $colored_example = str_replace(
        array("\n", "<code>", "</code>", "&nbsp;", "<br />"),
        array("", "", "", " ", "\n"),
        $colored_example
    );
    
    // Pre container to strip out uneeded font tags
    $colored_example = '<pre>' .  $colored_example . '</pre>';
    $colored_example = str_replace(
        array('<pre><font color="#000000">', '<pre><span style="color: #000000">', '</font></pre>', '</span></pre>'),
        array('', '', '', ''),
        $colored_example
    );

    // Get color settings
    $color_settings = array(
        '<span class="cs">' => '<font color="' . ini_get("highlight.string") . '">',
        '<span class="cc">' => '<font color="' . ini_get("highlight.comment") . '">',
        '<span class="ck">' => '<font color="' . ini_get("highlight.keyword") . '">',
        '<span class="cb">' => '<font color="' . ini_get("highlight.bg") . '">',
        '<span class="cd">' => '<font color="' . ini_get("highlight.default") . '">',
        '<span class="ch">' => '<font color="' . ini_get("highlight.html") . '">',
        '</span>'           => '</font>'
    );
    
    // Convert colors to classes spaned
    $colored_example = str_replace(
        array_values($color_settings),
        array_keys($color_settings),
        $colored_example
    );
    
    // Try to find function names so they can be linked
    // This patterns is what we are searching for:
    // <span class="cd">array_keys </span><span class="ck">(
    $colored_example = links2Examples(
        '!<span class="cd">([a-z0-9_]+)\s*</span><span class="ck">\(!Us',
        $colored_example,
        1,
        'cd'
    );

    // control structures or other keywords, like
    // exit, print with possibly something before
    // and after them

    // Note: I do not link if, else and the
    // other control structures in, because they are
    // too common, and linking them would clutter up
    // the examples
    $colored_example = links2Examples(
        '!<span class="ck">[^<]*(<br>)?[^<]*' .
        '(exit|die|echo|print|empty|isset|unset|break|continue' .
         '|static|global|array)[^<]*' .
        '</span>!Us',
        $colored_example,
        2,
        'ck'
    );
        
    // Return with the converted example
    return $linkwdiv . '<code id="example_' . $preNum . '">' . pre2code($colored_example) . '</code></div><p>';
   
} // formatPre() function end

// Convert <pre> contained code to text for the <code> container
function pre2code ($text)
{
    return str_replace(
        array('  ', "\n"),
        array(' &nbsp;', "<br>\n"),
        $text
    );
} // pre2code() function end

// Find string matching a regexp and make function/control
// structures/keywords links
function links2Examples($regexp, $example, $idx, $class)
{
    global $HTML_SRC;
    
    // Try to find matching text in $example
    if (preg_match_all($regexp, $example, $found)) {
    
        // This is where we store all text to replace,
        // [original text and replacement text]
        $replace_array = array(
            0 => array(),
            1 => array()
        );
        
        // Loop through all function names, and try to find a file
        // for them (they can be user defined functions)
        foreach ($found[$idx] as $num => $reptext) {
            
            // The main part of this filename
            $filepart = strtolower(str_replace("_", "-", $reptext));
            
            // Possible full filenames
            $files = array(
                "function.$filepart.html",
                "class.$filepart.html",
                "control-structures.$filepart.html"
            );

            // Guess what should be the filename for this
            foreach ($files as $filename) {
                
                // If this file exists, then we are OK
                if (@file_exists("$HTML_SRC/$filename")) {
                    $replace_array[0][] = $found[0][$num];
                    $replace_array[1][] = str_replace(
                        $reptext,
                        "<a href=\"$filename\" class=\"$class\">$reptext</a>",
                        $found[0][$num]
                    );
                    break;
                }
            }
        }
        
        // Perform string replacement on example content,
        // only replace functions where we can link
        $example = str_replace(
            $replace_array[0],
            $replace_array[1],
            $example
        );
        
    }

    // Maybe we modified something, maybe not.
    // Return with the current $example text.
    return $example;
                
} // links2Examples() function end

?>