<?php

  // USE ONLY PHP 4.x TO RUN THIS SCRIPT!!!
  // IT WONT WORK WITH PHP 3

  // SEE make_chm.README FOR INFORMATION!!!  

  ob_start();
  
  $fancydir = getenv("PHP_HELP_COMPILE_FANCYDIR");
  if (empty($fancydir)) { $fancydir = getenv("PHP_HELP_COMPILE_DIR"); }
  $language = getenv("PHP_HELP_COMPILE_LANG");
  $original_index = getenv("PHP_HELP_COMPILE_INDEX");

  MakeProjectFile();

  function MakeProjectFile () {

    global $fancydir, $language, $manual_title, $fancyindex, $indexfile, $original_index;

    // define language array (manual code -> HTML Help Code)
    // Japanese is not on my list, I don't know why...
    $languages = Array (
    
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

    if (file_exists("$fancydir/fancy-index.html")) { 
      $fancyindex = TRUE;
      $indexfile = "fancy-index.html";
    } else { $indexfile = $original_index; }
    
    // Start writing the project file
    $f = fopen ("manual-$language.hhp", "w");
    fputs ($f, "[OPTIONS]\n");
    fputs ($f, "Auto Index=Yes\n");
    fputs ($f, "Compatibility=1.1 or later\n");
    fputs ($f, "Compiled file=manual-$language-" . date("Ymd") . ".chm\n");
    fputs ($f, "Contents file=manual-$language.hhc\n");
    fputs ($f, "Default Font=Arial,10,0\n");
    fputs ($f, "Default Window=phpdoc\n");
    fputs ($f, "Default topic=$fancydir\\$indexfile\n");
    fputs ($f, "Display compile progress=Yes\n");
    fputs ($f, "Full-text search=Yes\n");
    
    // get the proper language code from the array
    fputs ($f, "Language=" . $languages[$language] . "\n");
    
    // now try to find out how the manual named in the actual language
    // this must be in the manual.html file as the title (DSSSL generated)
    $content = join("", file("$fancydir/$original_index"));
    if (preg_match("|>(.*)</TITLE|U", $content, $found)) {
      $manual_title = $found[1];
    } else { $manual_title = "PHP Manual"; }
    
    fputs ($f, "Title=$manual_title\n");
    
    // define the phpdoc window style (adds more functionality)
    fputs($f, "\n[WINDOWS]\nphpdoc=\"$manual_title\",\"manual-$language.hhc\",," .
    "\"$fancydir\\$indexfile\",\"$fancydir\\$indexfile\",,,,,0x23520,,0x386e,,,,,,,,0\n");

    // write out all the filenames as in $fancydir    
    fputs ($f, "\n[FILES]\n");
    $handle=opendir($fancydir);
    while (false!==($file = readdir($handle))) { 
      if ($file != "." && $file != "..") { 
         fputs ($f, "$fancydir\\$file\n"); 
      } 
    }
    closedir($handle); 
    fclose ($f);

  }
  
  function SiteMapObj ($name, $local, $tabs, $imgnum = 'auto') {

    global $fancydir;
    $name = htmlentities($name);

    echo "\n$tabs<LI> <OBJECT type=\"text/sitemap\">
$tabs  <param name=\"Name\" value=\"$name\">
$tabs  <param name=\"Local\" value=\"$fancydir\\$local\">";

    if ($imgnum != 'auto') { 
      echo "\n$tabs <param name=\"ImageNumber\" value=\"$imgnum\">";
    }
    echo "\n$tabs  </OBJECT>\n";
  
  }

  function DoFile ($filename) {

    global $fancydir;
    echo "    <UL>";
    $content = file ("$fancydir/$filename");
    for ($i = 0; $i < count ($content); $i++) {

      if (ereg ("><DT", $content[$i]) &&
          ereg ("><A", $content[$i+1]) &&
          ereg ("HREF=\"([a-z0-9-]+\.)+html(\#[0-9a-z\.-]+)?\"", $content[$i+2])) {

        preg_match ("/HREF=\"(([0-9a-z-]+\.)+html)(\#[0-9a-z\.-]+)?\"/", $content[$i+2], $matches);
        $param["html"] = $matches[1];
        if (isset($matches[3])) { $param["html"] .= $matches[3]; }

        if (ereg ("CLASS=\"literal\"", $content[$i+4])) {
          preg_match ("/>([^<]+)/", $content[$i+5], $matches);
        }
        elseif ($content[$i+2] == $content[$i+4]) {
          preg_match ("/>([^<]+)/", $content[$i+7], $matches);
        }
        else {
          preg_match ("/>([^<]+)/", $content[$i+3], $matches);
        }
        $param["title"] = $matches[1];
        SiteMapObj($param["title"], $param["html"], "      ");
      }

    }
    echo "    </UL>\n";
  }  
?>

<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<HTML>
<HEAD>
<meta name="GENERATOR" content="PHP 4 - Auto TOC script">
<!-- Sitemap 1.0 -->
</HEAD><BODY>
<OBJECT type="text/site properties">
  <param name="Window Styles" value="0x800227">
</OBJECT>
<UL>
<?php
  
  $index_a = file ("$fancydir/$original_index");
  $ijoin = join("", $index_a);
  $ijoin = preg_replace("/[\r|\n]{1,2}/", " ", $ijoin);
  
  // print out the objects, that autoparsing wont find
  // some automation may be there in the future
  
  SiteMapObj($manual_title, $indexfile, "  ", 21);

  if ($fancyindex) {
    preg_match('|CLASS=\"title\" ><A NAME=\"manual\" >(.*)</A|U', $ijoin, $match);
    SiteMapObj($match[1], "$original_index", "  ", 21);
  }

  preg_match('|<A HREF="preface.html" >(.*)</A >|U', $ijoin, $match);
  SiteMapObj($match[1], "preface.html", "  ");
  
  echo "\n  <UL>";
  preg_match('|<A HREF="preface.html#about" >(.*)</A >|U', $ijoin, $match);
  SiteMapObj($match[1], "preface.html#about", "    ");
  echo "  </UL>\n";
  
  // now autofind the chapters/subchapters
  
  for ($i = 0; $i < count ($index_a); $i++) {

    /* Chapters */
    if (ereg (">[IVX]+\.\ <A", $index_a[$i]) && !ereg ("HREF=\"ref\.[a-z0-9]+\.html", $index_a[$i+1])) {

      $new_list = 1;
      if ($not_closed == 1) { echo "\n  </UL>\n"; }

      //preg_match ("/>([IVX]+)\. <A/", $index_a[$i], $matches);
      //$chapter["nr"] = $matches[1];
      preg_match ("/HREF=\"([a-z0-9-]+\.html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
      $chapter["html"] = $matches[1];
      preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
      $chapter["title"] = $matches[1];
      SiteMapObj($chapter["title"], $chapter["html"], "  ");

    }

    /* Sub chapters */
    elseif (ereg (">([0-9]+|[IVXL]+|[A-Z])\.\ <A", $index_a[$i])) {

      if ($new_list == 1) {
        $new_list = 0;
        $not_closed = 1;
        echo "\n  <UL>\n";
      }
      
      //preg_match ("/>([0-9]+|[IVXL]+|[A-Z])\. <A/", $index_a[$i], $matches);
      //$schapter["nr"] = $matches[1];
      preg_match ("/HREF=\"([a-z0-9-]+\.([a-z0-9-]+\.)?html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
      $schapter["html"] = $matches[1];
      preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
      $schapter["title"] = $matches[1];
      SiteMapObj($schapter["title"], $schapter["html"], "    ");

      DoFile ($schapter["html"]);
    }   
  }

  echo "  </UL>\n";

  // link in directly the copyright page
  $cjoin = join("", file ("$fancydir/copyright.html"));
  $cjoin = preg_replace("/[\r|\n]{1,2}/", " ", $cjoin);
  preg_match('|<A NAME="copyright" ></A ><P ><B >(.*)</B|U', $cjoin, $match);
  SiteMapObj($match[1], "copyright.html", "  ", 17);

?>
</UL>
</BODY></HTML>

<?php

  // grab all the output at this point and
  // write out to the proper language .hcc file
  $writeout = ob_get_contents();
  $fp = fopen("manual-$language.hhc", "w");
  fputs($fp, $writeout);
  fclose($fp);
  
  /* 

     Now no index is made, so no need to print out a blank file.
     In the future, we should make an index file somehow. 

  // make a default index file (no content, no index)
  $index_hhk = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<HTML>
<HEAD>
<meta name="GENERATOR" content="PHP 4 - Auto TOC script">
<!-- Sitemap 1.0 -->
</HEAD><BODY>
<UL>
</UL>
</BODY></HTML>';

  $fp = fopen("index.hhk", "w");
  fputs($fp, $index_hhk);
  fclose($fp); 

  */
  
?>
