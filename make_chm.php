<?php

// USE ONLY PHP 4.x TO RUN THIS SCRIPT!!!
// IT WONT WORK WITH PHP 3

// SEE make_chm.README FOR INFORMATION!!!  

$fancydir = getenv("PHP_HELP_COMPILE_FANCYDIR");
if (empty($fancydir)) {
    $fancydir = getenv("PHP_HELP_COMPILE_DIR");
}
$language       = getenv("PHP_HELP_COMPILE_LANG");
$original_index = "index.html";

// header for index and toc 
$header = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
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

MakeProjectFile();
MakeContentFiles();

/***********************************************************************/
/* End of script lines, function follows                               */
/***********************************************************************/

// Generate the HTML Help content files 
function MakeContentFiles()
{
    global $fancydir, $language, $manual_title, $fancyindex, $indexfile, $original_index, $header;

    $toc = fopen("php_manual_$language.hhc", "w");
    $index = fopen("php_manual_$language.hhk", "w");

	$index_entries = array();
    
    // Write out file headers
    fputs($toc, $header);
    fputs($index, $header);

    // Read original index file and drop out newlines
    $index_a = file("$fancydir/$original_index");
    $ijoin = join("", $index_a);
    $ijoin = preg_replace("/[\r|\n]{1,2}/", " ", $ijoin);

    // Print out the objects, autoparsing won't find
    SiteMapObj($manual_title, $indexfile, "    ", $toc, 21);
    $index_entries[$indexfile] = $manual_title;

    // Find the name of the Table of Contents
    if ($fancyindex) {
        preg_match('|CLASS=\"title\" ><A NAME=\"manual\" >(.*)</A|U', $ijoin, $match);
        if (empty($match[1])) { // Fallback
            $match[1] = "Table of Contents";
        }
        SiteMapObj($match[1], $original_index, "    ", $toc, 21);
        $index_entries[$original_index] = $match[1];
    }

    // Find the name of the Preface
    preg_match('|<A HREF="preface.html" >(.*)</A >|U', $ijoin, $match);
    if (empty($match[1])) { // Fallback
        $match[1] = "Preface";
    }
    SiteMapObj($match[1], "preface.html", "    ", $toc);
    $index_entries["preface.html"] = $match[1];

    // Find the name of the Preface/About this Manual
    fputs($toc, "\n    <ul>");
    preg_match('|<A HREF="preface.html#about" >(.*)</A >|U', $ijoin, $match);
    if (empty($match[1])) { // Fallback
        $match[1]="About this Manual";
    }
    SiteMapObj($match[1], "preface.html#about", "      ", $toc);
    $index_entries["preface.html#about"] = $match[1];
    fputs($toc, "    </ul>\n");

    // Now autofind the chapters/subchapters
    $not_closed = 0;

    for ($i = 0; $i < count ($index_a); $i++) {
        
        /* Chapters */
        if (ereg(">[IVXLC]+\.\ <A", $index_a[$i]) && !ereg("HREF=\"ref\.[a-z0-9]+\.html", $index_a[$i+1])) {

            $new_list = 1;
            if ($not_closed == 1) {
                fputs($toc, "\n    </ul>\n");
            }

            //preg_match ("/>([IVX]+)\. <A/", $index_a[$i], $matches);
            //$chapter["nr"] = $matches[1];
            preg_match("/HREF=\"([a-z0-9-]+\.html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
            $chapter["html"] = $matches[1];
            preg_match("/>([^<]+)/", $index_a[$i+2], $matches);
            $chapter["title"] = $matches[1];
            SiteMapObj($chapter["title"], $chapter["html"], "    ", $toc);
            $index_entries[$chapter["html"]] = $chapter["title"];
        }

        /* Sub chapters */
        else if (ereg(">([0-9]+|[IVXLC]+|[A-Z])\.\ <A", $index_a[$i])) {

            if ($new_list == 1) {
                $new_list = 0;
                $not_closed = 1;
                fputs($toc, "\n    <ul>\n");
            }

            //preg_match ("/>([0-9]+|[IVXLC]+|[A-Z])\. <A/", $index_a[$i], $matches);
            //$schapter["nr"] = $matches[1];
            preg_match("/HREF=\"([a-z0-9-]+\.([a-z0-9-]+\.)?html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
            $schapter["html"] = $matches[1];
            preg_match("/>([^<]+)/", $index_a[$i+2], $matches);
            $schapter["title"] = $matches[1];
            SiteMapObj($schapter["title"], $schapter["html"], "      ", $toc);
            $index_entries[$chapter["html"]] = $schapter["title"];

            DoFile($schapter["html"], $index_entries, $toc);
        }
    }

    fputs($toc, "  </ul>\n");

    // Link in directly the copyright page
    $cjoin = join("", file("$fancydir/copyright.html"));
    $cjoin = preg_replace("/[\r|\n]{1,2}/", " ", $cjoin);
    preg_match('|<A NAME="copyright" ></A ><P ><B >(.*)</B|U', $cjoin, $match);
    if (empty($match[1])) { // fallback
        $match[1] = "Copyright";
    }
    SiteMapObj($match[1], "copyright.html", "    ", $toc, 17);
    $index_entries["copyright.html"] = $match[1];

	$index_entries = array_unique($index_entries);
	natcasesort($index_entries);
	
	foreach ($index_entries as $local => $name) {
	    $name = str_replace('"', '&quot;', $name);
	    
	    fputs($index, "
	    <li><object type=\"text/sitemap\">
	      <param name=\"Local\" value=\"$fancydir\\$local\">
	      <param name=\"Name\" value=\"$name\">
	    </object></li>");
	}

    // Write out closing line, and end files
    fputs($index, "  </ul>\n</body>\n</html>");
    fputs($toc, "  </ul>\n</body>\n</html>");

    fclose($index);
    fclose($toc);
} // MakeContentfiles() function end

// Generates the HTML Help project file
function MakeProjectFile()
{
    global $fancydir, $language, $manual_title, $fancyindex, $indexfile, $original_index;

    // define language array (manual code -> HTML Help Code)
    $languages = array(
        "cs"    => "0x405 Czech",
        "de"    => "0x407 German (Germany)",
        "en"    => "0x809 Enlish (United Kingdom)",
        "es"    => "0xc0a Spanish (International Sort)",
        "fr"    => "0x40c French (France)",
        "hu"    => "0x40e Hungarian",
        "it"    => "0x410 Italian (Italy)",
        "ja"    => "0x411 Japanese",
        "kr"    => "0x412 Korean",
        "nl"    => "0x413 Dutch (Netherlands)",
        "pt_BR" => "0x416 Portuguese (Brazil)"
    );

    // Try to find the fancy index file
    if (file_exists("$fancydir/fancy-index.html")) {
        $fancyindex = TRUE;
        $indexfile = "fancy-index.html";
    } else {
        $indexfile = $original_index;
    }

    // Start writing the project file
    $project = fopen("php_manual_$language.hhp", "w");
    fputs($project, "[OPTIONS]\n");
    fputs($project, "Compatibility=1.1 or later\n");
    fputs($project, "Compiled file=php_manual_$language.chm\n");
    fputs($project, "Contents file=php_manual_$language.hhc\n");
    fputs($project, "Index file=php_manual_$language.hhk\n");
    fputs($project, "Default Font=Arial,10,0\n");
    fputs($project, "Default Window=phpdoc\n");
    fputs($project, "Default topic=$fancydir\\$indexfile\n");
    fputs($project, "Display compile progress=Yes\n");
    fputs($project, "Full-text search=Yes\n");

    // Get the proper language code from the array
    fputs($project, "Language=" . $languages[$language] . "\n");

    // Now try to find out how the manual named in the actual language
    // this must be in the index.html file as the title (DSSSL generated)
    $content = join("", file("$fancydir/$original_index"));
    if (preg_match("|>(.*)</TITLE|U", $content, $found)) {
        $manual_title = $found[1];
    } else { // Fallback
        $manual_title = "PHP Manual";
    }

    fputs($project, "Title=$manual_title\n");

    // Define the phpdoc window style (adds more functionality)
    fputs($project, "\n[WINDOWS]\nphpdoc=\"$manual_title\",\"php_manual_$language.hhc\",\"php_manual_$language.hhk\"," .
          "\"$fancydir\\$indexfile\",\"$fancydir\\$indexfile\",,,,,0x23520,,0x386e,,,,,,,,0\n");

    // Write out all the filenames as in $fancydir
    fputs($project, "\n[FILES]\n");
    $handle=opendir($fancydir);
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            fputs($project, "$fancydir\\$file\n");
        }
    }
    closedir($handle);
    fclose($project);
} // MakeProjectFile() function end

// Print out a SiteMap object for a file
function SiteMapObj($name, $local, $tabs, $toc, $imgnum = "auto")
{
    global $fancydir;
    $name = str_replace('"', '&quot;', $name);

    fputs($toc, "
$tabs<li><object type=\"text/sitemap\">
$tabs  <param name=\"Name\" value=\"$name\">
$tabs  <param name=\"Local\" value=\"$fancydir\\$local\">
");

    if ($imgnum != "auto") {
        fputs($toc, "$tabs  <param name=\"ImageNumber\" value=\"$imgnum\">\n");
    }
    fputs($toc, "$tabs  </object>\n");
} // SiteMapObj() function end

// Process a file, and find any links need to be presented in tree
function DoFile ($filename, &$index_entries, $toc)
{
    global $fancydir;
    $content = file ("$fancydir/$filename");
    $contents = preg_replace("/[\n|\r]/", " ", join("", $content));
    
    // Find all sublinks
    if (preg_match_all("!<DT\s+><A\s+HREF=\"(([\w\.-]+\.)+html)(\#[\w\.-]+)?\"\s+>(.+)</A\s+>!U", $contents, $matches, PREG_SET_ORDER)) {
        
        // Print out the file informations for all the links
        fputs($toc, "    <ul>");
        foreach ($matches as $onematch) {
            $param["html"] = $onematch[1];
            if (!empty($onematch[3])) {
                $param["html"] .= $onematch[3];
            }
            $param["title"] = strip_tags($onematch[4]);
            SiteMapObj($param["title"], $param["html"], "      ", $toc);
            $index_entries[$param["html"]] = $param["title"];
        }
        fputs($toc, "    </ul>\n");

    } else {

        // Uncomment this if you want to debug the above pregs
        // Note: there are many files normally without deeper
        // TOC info, eg. language.expressions.html

        // echo "no deeper TOC info found in $filename\n";
        // return;

    }
    
} // DoFile() function end
?>