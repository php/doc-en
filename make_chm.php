<?php

	// USE ONLY PHP 4.x TO RUN THIS SCRIPT!!!
	// IT WONT WORK WITH PHP 3
	
	/* input :::::: we need three env params
		
		  PHP_HELP_COMPILER
		    hcc.exe path including hcc.exe (eg. d:\hhw\hcc.exe)
		  PHP_HELP_COMPILE_DIR
		    dir where the html manual resides (eg. html)
		  PHP_HELP_COMPILE_LANG
		    the actual manual language (eg. hu)
		
		output :::::: we write out four files needed to compile
		
		  manual_lang.hhp :: manual project file (lang comes from env)
			manual_lang.hhc :: manual contents file
			index.hhk       :: just a dummy epmty index file
			compile.bat     :: a call to the compiler (lang used)

	*/
	
	ob_start();
	
	$htmldir = getenv("PHP_HELP_COMPILE_DIR");
	$language = getenv("PHP_HELP_COMPILE_LANG");

	MakeProjectFile();

	function MakeProjectFile () {

		global $htmldir, $language, $manual_title;

		// define language array (manual code -> HTML Help Code)
		$languages = Array (
		
			"de"    => "0x407 German (Germany)",
			"en"    => "0x809 Enlish (United Kingdom)",
			"es"    => "0xc0a Spanish (International Sort)",
			"fr"    => "0x40c French (France)",
			"hu"    => "0x40e Hungarian",
			"il"    => "0x410 Italian (Italy)",
			"kr"    => "0x412 Korean",
			"nl"    => "0x413 Dutch (Netherlands)",
			"pt_BR" => "0x416 Portuguese (Brazil)"
		
		);

		// Start writing the project file
		$f = fopen ("manual_$language.hhp", "w");
		fputs ($f, "[OPTIONS]\n");
		fputs ($f, "Auto Index=Yes\n");
		fputs ($f, "Binary TOC=Yes\n");
		fputs ($f, "Compatibility=1.1 or later\n");
		fputs ($f, "Compiled file=manual_$language.chm\n");
		fputs ($f, "Contents file=manual_$language.hhc\n");
		fputs ($f, "Default Font=Arial,10,0\n");
		fputs ($f, "Default topic=$htmldir\manual.html\n");
		fputs ($f, "Display compile progress=Yes\n");
		fputs ($f, "Full-text search=Yes\n");
		fputs ($f, "Index file=index.hhk\n");
		
		// get the proper language code from the array
		fputs ($f, "Language=" . $languages[$language] . "\n");
		
		// now try to find out how the manual named in the actual language
		// this must be in the manual.html file as the title (DSSSL generated)
		$content = join("", file("$htmldir/manual.html"));
		$content = preg_replace("/[\\r|\\n]/", "", $content);
		if (preg_match("|<TITLE>(.*)</TITLE>|U", $content, $found)) {
			$manual_title = $found[1];
		} else { $manual_title = "PHP Manual"; }
		
		fputs ($f, "Title=$manual_title\n");
		
		// write out all the filenames as in $htmldir		
		fputs ($f, "\n[FILES]\n");
		$handle=opendir($htmldir);
		while (false!==($file = readdir($handle))) { 
			if ($file != "." && $file != "..") { 
				 fputs ($f, "$htmldir\\$file\n"); 
			} 
		}
		closedir($handle); 
		fclose ($f);

	}
	
	function SiteMapObj ($name, $local, $tabs, $imgnum = 'auto') {

		global $htmldir;
		echo "\n$tabs<LI> <OBJECT type=\"text/sitemap\">
$tabs	<param name=\"Name\" value=\"$name\">
$tabs	<param name=\"Local\" value=\"$htmldir\\$local\">";

		if ($imgnum != 'auto') { 
			echo "\n$tabs <param name=\"ImageNumber\" value=\"$imgnum\">";
		}
		echo "\n$tabs	</OBJECT>\n";
	
	}

	function DoFile ($filename) {

		global $htmldir;
		echo "		<UL>";
		if (file_exists("$htmldir/$filename")) { 
			$content = file ("$htmldir/$filename");
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
					SiteMapObj($param["title"], $param["html"], "			");
				}
	
			}
		}
		echo "		</UL>\n";
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
	<param name="ImageType" value="Folder">
</OBJECT>
<UL>
<?php
	
	$index_a = file ("html/manual.html");
	$ijoin = join("", $index_a);
	$ijoin = preg_replace("/[\r|\n]/", " ", $ijoin);
	
	// print out the objects autoparsing didnt find
	// some automation may there in the future
	
	preg_match('|<DIV CLASS="TOC" ><DL ><DT ><B >(.*)</B >|U', $ijoin, $match);
	SiteMapObj($match[1], "manual.html", "	", 21);

	preg_match('|<A HREF="preface.html" >(.*)</A >|U', $ijoin, $match);
	SiteMapObj($match[1], "preface.html", "	");
	
	echo "\n	<UL>";
	preg_match('|<A HREF="preface.html#about" >(.*)</A >|U', $ijoin, $match);
	SiteMapObj($match[1], "preface.html#about", "		");
	echo "	</UL>\n";
	
	// now autofind the chapters/subchapters
	
	for ($i = 0; $i < count ($index_a); $i++) {

		/* Chapters */
		if (ereg (">[IVX]+\.\ <A", $index_a[$i]) && !ereg ("HREF=\"ref\.[a-z]+\.html", $index_a[$i+1])) {

			$new_list = 1;
			if ($not_closed == 1) { echo "\n	</UL>\n"; }

			//preg_match ("/>([IVX]+)\. <A/", $index_a[$i], $matches);
			//$chapter["nr"] = $matches[1];
			preg_match ("/HREF=\"([a-z0-9-]+\.html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
			$chapter["html"] = $matches[1];
			preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
			$chapter["title"] = $matches[1];
			SiteMapObj($chapter["title"], $chapter["html"], "	");

		}

		/* Sub chapters */
		elseif (ereg (">([0-9]+|[IVXL]+|[A-Z])\.\ <A", $index_a[$i])) {

			if ($new_list == 1) {
				$new_list = 0;
				$not_closed = 1;
				echo "\n	<UL>\n";
			}
			
			//preg_match ("/>([0-9]+|[IVXL]+|[A-Z])\. <A/", $index_a[$i], $matches);
			//$schapter["nr"] = $matches[1];
			preg_match ("/HREF=\"([a-z0-9-]+\.([a-z0-9-]+\.)?html)(\#[a-z0-9]+)?\"/", $index_a[$i+1], $matches);
			$schapter["html"] = $matches[1];
			preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
			$schapter["title"] = $matches[1];
			SiteMapObj($schapter["title"], $schapter["html"], "		");

			DoFile ($schapter["html"]);
		} 	
	}

	echo "	</UL>\n";

	// link in directly the copyright page
	preg_match('|<A HREF="copyright.html" >(.*)</A > &copy;|U', $ijoin, $match);
	SiteMapObj($match[1], "copyright.html", "	", 17);

?>
</UL>
</BODY></HTML>

<?php

	// grab all the output at this point and
	// write out to the proper language .hcc file
	$writeout = ob_get_contents();
	$fp = fopen("manual_$language.hhc", "w");
	fputs($fp, $writeout);
	fclose($fp);
	
	// make a compile.bat file according to the actual language
	$fp = fopen("compile.bat", "w");
	fputs($fp, "@" . getenv("PHP_HELP_COMPILER") . " manual_$language.hhp\n");
	fclose($fp);
	
	// make a default index file (no content, no index)
	// this is needed by the compiler, to compile errorfree ...
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

?>
