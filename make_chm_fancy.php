<?php

  set_time_limit(0);
  
  $htmldir = getenv("PHP_HELP_COMPILE_DIR");
  $fancydir = getenv("PHP_HELP_COMPILE_FANCYDIR");
  $language = getenv("PHP_HELP_COMPILE_LANG");
  $original_index = getenv("PHP_HELP_COMPILE_INDEX");

  $counter = 0;
  
  $handle=opendir($htmldir);
  while (false!==($filename = readdir($handle))) { 
    if (strpos($filename, ".html") && ($filename != "fancy-index.html")) {
      fancy_design($filename);
    } 
  }
  closedir($handle); 
  
  // make GENTIME the actual date/time
  $content = join("", file("make_chm_index_$language.html"));
  $content = preg_replace("/\\[GENTIME\\]/", date("D M d H:i:s Y"), $content);  
  $fp = fopen("$fancydir/fancy-index.html", "w");
  fputs($fp, $content);
  fclose($fp);
  
  copy("make_chm_style.css", "$fancydir/style.css");
  copy("make_chm_spc.gif", "$fancydir/spacer.gif");

  $counter += 3;
  
  echo "\nConverting ready...\n";
  echo "Total number of files written in $fancydir directory: $counter\n\n";
  
  function fancy_design($fname) {
    
    global $htmldir, $fancydir, $counter, $original_index;

    // get the contents of the file from $htmldir
    $content = join("", file("$htmldir/$fname"));
  
    // css file linking
    $content = preg_replace("|</HEAD|", '<LINK REL="stylesheet" HREF="style.css"></HEAD', $content);

    // no margins around
    $content = preg_replace("/<BODY/", '<BODY TOPMARGIN="0" LEFTMARGIN="0"', $content);
    
    // HR dropout
    $content = preg_replace("/<HR\\s+ALIGN=\"LEFT\"\\s+WIDTH=\"100%\">/", '', $content);

    // whole page table and backgrounds
    $wpbegin = '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3">';
    $bnavt = '<TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">';
    $lnavt = '<TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR>';
    $space = '<IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1">';
    
    // navheader backgound
    $content = preg_replace("/<DIV\\s+CLASS=\"NAVHEADER\"\\s+><TABLE(.*)CELLPADDING=\"0\"(.*)<\\/TABLE\\s+><\\/DIV\\s+>/Us",
      $wpbegin . '<DIV CLASS="NAVHEADER">' . $bnavt . '<TR><TD><TABLE\\1CELLPADDING="3"\\2</TABLE></TD></TR>' . $lnavt . '</TABLE></DIV></TD></TR><TR><TD>' . $space . '</TD><TD HEIGHT="100%" VALIGN="TOP" WIDTH="100%"><BR>', $content);

    // navfooter backgound
    $content = preg_replace("/<DIV\\s+CLASS=\"NAVFOOTER\"\\s+><TABLE(.*)CELLPADDING=\"0\"(.*)<\\/TABLE\\s+><\\/DIV\\s+>/Us",
      '<BR></TD><TD>' . $space . '</TD></TR><TR><TD COLSPAN="3"><DIV CLASS="NAVFOOTER">' . $bnavt . $lnavt . '<TR><TD><TABLE\\1CELLPADDING="3"\\2</TABLE></TD></TR></TABLE></DIV></TD></TR></TABLE>', $content);
      
    // fix copyright page fault...
    if ($fname == "copyright.html") {
      $content = preg_replace("/&#38;copy;/", "&copy;", $content);
      $content = preg_replace("/<A\\s+HREF=\"$original_index#(authors|translators)\"/U", "<A HREF=\"fancy-index.html\"", $content);
      $content = preg_replace("|(</TH\\s+></TR\\s+>)|", "\\1<TR><TH COLSPAN=\"3\" ALIGN=\"center\">&nbsp;</TH></TR>", $content);
      $content = preg_replace("|(&nbsp;</TD\\s+></TR\\s+>)|", "\\1<TR><TD COLSPAN=\"3\" ALIGN=\"center\">&nbsp;</TD></TR>", $content);
    }
    
    // fix the original manual index to look far better...
    elseif ($fname == "$original_index") {
      $indexchange = '<TABLE BORDER="0" WIDTH="100%" HEIGHT="100%" CELLSPACING="0" CELLPADDING="0"><TR><TD COLSPAN="3"><DIV CLASS="NAVHEADER"><TABLE BGCOLOR="#CCCCFF" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%"><TR><TD><TABLE
      WIDTH="100%" BORDER="0" CELLPADDING="3" CELLSPACING="0"><TR><TH COLSPAN="3">PHP Kézikönyv</TH></TR><TR><TD COLSPAN="3" ALIGN="center">&nbsp;</TD></TR></TABLE></TD></TR><TR BGCOLOR="#333366"><TD><IMG SRC="spacer.gif" BORDER="0" WIDTH="1" HEIGHT="1"><BR></TD></TR></TABLE>
      </DIV></TD></TR><TR><TD><IMG SRC="spacer.gif" WIDTH="10" HEIGHT="1"></TD><TD HEIGHT="100%" VALIGN="TOP" WIDTH="100%"><BR>';
      $content = preg_replace("/(<DIV\\s+CLASS=\"BOOK\")/", "$indexchange\\1", $content);
      $content = preg_replace("/(<DIV\\s+CLASS=\"author\").*<HR>/Us", "", $content);
      preg_match('|<DIV\\s+CLASS="TOC"\\s+><DL\\s+><DT\\s+><B\\s+>(.*)</B\\s+>|U', $content, $match);
      $content = preg_replace("|(CLASS=\"title\"\\s+><A\\s+NAME=\"manual\"\\s+>).*(</A)|U", "\\1$match[1]\\2", $content);
      $content = preg_replace("|<DT\\s+><B\\s+>(.*)</B\\s+></DT\\s+>|U", "", $content);
      
    }

    // print out that new file to $fancydir
    $fp = fopen("$fancydir/$fname", "w");
    fputs($fp, $content);
    fclose($fp);
    
    // print out a message to see the progress
    echo "$fancydir/$fname ready...\n";
    $counter++;
    
  }

?>
