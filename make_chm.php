<?php

// USE ONLY PHP 4.x TO RUN THIS SCRIPT!!!
// IT WONT WORK WITH PHP 3

// SEE make_chm.README FOR INFORMATION!!!  

$fancydir = getenv("PHP_HELP_COMPILE_FANCYDIR");
if(empty($fancydir))
{
	$fancydir = getenv("PHP_HELP_COMPILE_DIR");
}
$language = getenv("PHP_HELP_COMPILE_LANG");
$original_index = getenv("PHP_HELP_COMPILE_INDEX");

// header for index and toc 
$header = "
		  <!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML//EN\">
		  <HTML>
		  <HEAD>
		  <meta name=\"GENERATOR\" content=\"PHP 4 - Auto TOC script\">
		  <!-- Sitemap 1.0 -->
		  </HEAD>
		  <BODY>
		  <OBJECT type=\"text/site properties\">
		  <param name=\"Window Styles\" value=\"0x800227\">
		  </OBJECT>
		  <UL>
		  ";

MakeProjectFile();
MakeContentFiles();


/* end */

/* functions */


function MakeContentFiles()
{
	global $fancydir, $language, $manual_title, $fancyindex, $indexfile, $original_index, $header;

	$toc = fopen("manual-$language.hhc", "w");
	$index = fopen("manual-$language.hhk", "w");

	fputs($toc, $header);
	fputs($index, $header);

	$index_a = file("$fancydir/$original_index");
	$ijoin = join("", $index_a);
	$ijoin = preg_replace("/[\r|\n]{1,2}/", " ", $ijoin);

	// print out the objects, that autoparsing wont find
	// some automation may be there in the future

	SiteMapObj($manual_title, $indexfile, "  ", $toc, 21);
	IndexObj($manual_title, $indexfile, $index);

	if($fancyindex)
	{
		preg_match('|CLASS=\"title\" ><A NAME=\"manual\" >(.*)</A|U', $ijoin, $match);
		if(empty($match[1]))
		{
			// fallback
			$match[1]="Table of Contents";
		}
		SiteMapObj($match[1], $original_index, "  ", $toc, 21);
		IndexObj($match[1], $original_index, $index);
	}

	preg_match('|<A HREF="preface.html" >(.*)</A >|U', $ijoin, $match);
	if(empty($match[1]))
	{
		// fallback
		$match[1]="Preface";
	}
	SiteMapObj($match[1], "preface.html", "  ", $toc);
	IndexObj($match[1], "preface.html", $index);

	fputs($toc, "\n  <UL>");
	preg_match('|<A HREF="preface.html#about" >(.*)</A >|U', $ijoin, $match);
	if(empty($match[1]))
	{
		// fallback
		$match[1]="About this Manual";
	}
	SiteMapObj($match[1], "preface.html#about", "    ", $toc);
	IndexObj($match[1], "preface.html#about", $index);
	fputs($toc, "  </UL>\n");

	// now autofind the chapters/subchapters

	$not_closed = 0;

	for($i = 0; $i < count ($index_a); $i++)
	{
		/* Chapters */
		if(ereg(">[IVX]+\.\ <A", $index_a[$i]) && !ereg("HREF=\"ref\.[a-z0-9]+\.html", $index_a[$i+1]))
		{

			$new_list = 1;
			if($not_closed == 1)
			{
				fputs($toc, "\n  </UL>\n");
			}

			//preg_match ("/>([IVX]+)\. <A/", $index_a[$i], $matches);
			//$chapter["nr"] = $matches[1];
			preg_match("/HREF=\"([a-z0-9-]+\.html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
			$chapter["html"] = $matches[1];
			preg_match("/>([^<]+)/", $index_a[$i+2], $matches);
			$chapter["title"] = $matches[1];
			SiteMapObj($chapter["title"], $chapter["html"], "  ", $toc);
			IndexObj($chapter["title"], $chapter["html"], $index);
		}

		/* Sub chapters */
		else if(ereg(">([0-9]+|[IVXL]+|[A-Z])\.\ <A", $index_a[$i]))
		{
			if($new_list == 1)
			{
				$new_list = 0;
				$not_closed = 1;
				fputs($toc, "\n  <UL>\n");
			}

			//preg_match ("/>([0-9]+|[IVXL]+|[A-Z])\. <A/", $index_a[$i], $matches);
			//$schapter["nr"] = $matches[1];
			preg_match("/HREF=\"([a-z0-9-]+\.([a-z0-9-]+\.)?html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
			$schapter["html"] = $matches[1];
			preg_match("/>([^<]+)/", $index_a[$i+2], $matches);
			$schapter["title"] = $matches[1];
			SiteMapObj($schapter["title"], $schapter["html"], "    ", $toc);
			IndexObj($chapter["title"], $schapter["html"], $index);

			DoFile($schapter["html"], $toc, $index);
		}
	}

	fputs($toc, "  </UL>\n");

	// link in directly the copyright page
	$cjoin = join("", file("$fancydir/copyright.html"));
	$cjoin = preg_replace("/[\r|\n]{1,2}/", " ", $cjoin);
	preg_match('|<A NAME="copyright" ></A ><P ><B >(.*)</B|U', $cjoin, $match);
	if(empty($match[1]))
	{
		// fallback
		$match[1]="Copyright";
	}
	SiteMapObj($match[1], "copyright.html", "  ", $toc, 17);
	IndexObj($match[1], "copyright.html", $index);

	fputs($index, "</UL>\n</BODY></HTML>");
	fputs($toc, "</UL>\n</BODY></HTML>");

	fclose($index);
	fclose($toc);
}







function MakeProjectFile()
{
	global $fancydir, $language, $manual_title, $fancyindex, $indexfile, $original_index;

	// define language array (manual code -> HTML Help Code)
	// Japanese is not on my list, I don't know why...
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

	if(file_exists("$fancydir/fancy-index.html"))
	{
		$fancyindex = TRUE;
		$indexfile = "fancy-index.html";
	}
	else
	{
		$indexfile = $original_index;
	}

	// Start writing the project file
	$project = fopen("manual-$language.hhp", "w");
	fputs($project, "[OPTIONS]\n");
	fputs($project, "Compatibility=1.1 or later\n");
	fputs($project, "Compiled file=manual-$language-" . date("Ymd") . ".chm\n");
	fputs($project, "Contents file=manual-$language.hhc\n");
	fputs($project, "Index file=manual-$language.hhk\n");
	fputs($project, "Default Font=Arial,10,0\n");
	fputs($project, "Default Window=phpdoc\n");
	fputs($project, "Default topic=$fancydir\\$indexfile\n");
	fputs($project, "Display compile progress=Yes\n");
	fputs($project, "Full-text search=Yes\n");

	// get the proper language code from the array
	fputs($project, "Language=" . $languages[$language] . "\n");

	// now try to find out how the manual named in the actual language
	// this must be in the manual.html file as the title (DSSSL generated)
	$content = join("", file("$fancydir/$original_index"));
	if(preg_match("|>(.*)</TITLE|U", $content, $found))
	{
		$manual_title = $found[1];
	}
	else
	{
		$manual_title = "PHP Manual";
	}

	fputs($project, "Title=$manual_title\n");

	// define the phpdoc window style (adds more functionality)
	fputs($project, "\n[WINDOWS]\nphpdoc=\"$manual_title\",\"manual-$language.hhc\",\"manual-$language.hhk\"," .
		  "\"$fancydir\\$indexfile\",\"$fancydir\\$indexfile\",,,,,0x23520,,0x386e,,,,,,,,0\n");

	// write out all the filenames as in $fancydir    
	fputs($project, "\n[FILES]\n");
	$handle=opendir($fancydir);
	while(false!==($file = readdir($handle)))
	{
		if($file != "." && $file != "..")
		{
			fputs($project, "$fancydir\\$file\n"); 
		}
	}
	closedir($handle); 
	fclose($project);
}

function SiteMapObj($name, $local, $tabs, $toc, $imgnum = "auto")
{

	global $fancydir;
	/* should not be needed because if the documentation is valid xhtml $name would already
	 * be html encoded.
	 */
//	$name = htmlentities($name);

	fputs($toc, "\n$tabs<LI> <OBJECT type=\"text/sitemap\">
		  $tabs  <param name=\"Name\" value=\"$name\">
		  $tabs  <param name=\"Local\" value=\"$fancydir\\$local\">");

	if($imgnum != "auto")
	{
		fputs($toc, "\n$tabs <param name=\"ImageNumber\" value=\"$imgnum\">");
	}
	fputs($toc, "\n$tabs  </OBJECT>\n");
}

function IndexObj($name, $local, $index)
{

	global $fancydir;
	/* should not be needed because if the documentation is valid xhtml $name would already
	 * be html encoded.
	 */
//	$name = htmlentities($name);

	fputs($index, "\n<LI><OBJECT type=\"text/sitemap\">
		  <param name=\"Local\" value=\"$fancydir\\$local\">
		  <param name=\"Name\" value=\"$name\">
		  </OBJECT></LI>");
}

function DoFile ($filename, $toc, $index)
{
   global $fancydir;
   fputs($toc, "    <UL>");
   $content = file ("$fancydir/$filename");
   for($i = 0; $i < count ($content); $i++)
   {
	  if(ereg ("><DT", $content[$i]) &&
		 ereg ("><A", $content[$i+1]) &&
		 ereg ("HREF=\"([a-z0-9-]+\.)+html(\#[0-9a-z\.-]+)?\"", $content[$i+2]))
	  {
		 preg_match ("/HREF=\"(([0-9a-z-]+\.)+html)(\#[0-9a-z\.-]+)?\"/", $content[$i+2], $matches);
		 $param["html"] = $matches[1];
		 if(isset($matches[3]))
		 {
			$param["html"] .= $matches[3];
		 }
   
		 if(ereg ("CLASS=\"literal\"", $content[$i+4]))
		 {
			preg_match ("/>([^<]+)/", $content[$i+5], $matches);
		 }
		 else if($content[$i+2] == $content[$i+4])
		 {
			preg_match ("/>([^<]+)/", $content[$i+7], $matches);
		 }
		 else
		 {
			preg_match ("/>([^<]+)/", $content[$i+3], $matches);
		 }
		 $param["title"] = $matches[1];
		 SiteMapObj($param["title"], $param["html"], "      ", $toc);
		 IndexObj($param["title"], $param["html"], $index);
	  }
   }
   fputs($toc, "    </UL>\n");
}  
?>
		 