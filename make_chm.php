<?php
	function MakeProjectFile ()
	{
		$f = fopen ("manual.hhp", "w");
		fputs ($f, "[OPTIONS]\n");
		fputs ($f, "Contents file=manual.hhc\n");
		fputs ($f, "Auto Index=Yes\n");
		fputs ($f, "Binary TOC=Yes\n");
		fputs ($f, "Compatibility=1.1 or later\n");
		fputs ($f, "Compiled file=manual.chm\n");
		fputs ($f, "Contents file=manual.hhc\n");
		fputs ($f, "Default Font=Arial,10,0\n");
		fputs ($f, "Default topic=html\manual.html\n");
		fputs ($f, "Display compile progress=Yes\n");
		fputs ($f, "Full-text search=Yes\n");
		fputs ($f, "Index file=Index.hhk\n");
		fputs ($f, "Language=0x413 Nederlands (standaard)\n");
		fputs ($f, "Title=PHP Manual\n");
		fputs ($f, "\n[FILES]\n");
		$handle=opendir('manual'); 
		while (false!==($file = readdir($handle))) { 
			if ($file != "." && $file != "..") { 
				 fputs ($f, "html\\$file\n"); 
			} 
		}
		closedir($handle); 
		fclose ($f);
	}
	
	MakeProjectFile();

	function DoFile ($filename)
	{
?>
		<UL>
<?php
		$ar = file ($filename);
		for ($i = 0; $i < count ($ar); $i++)
		{
			if (ereg ("><DT", $ar[$i]) &&
			    ereg ("><A", $ar[$i+1]) &&
			    ereg ("HREF=\"([a-z0-9-]+\.)+html(\#[0-9a-z\.-]+)?\"", $ar[$i+2]))
			{
				preg_match ("/HREF=\"(([0-9a-z-]+\.)+html)(\#[0-9a-z\.-]+)?\"/", $ar[$i+2], $matches);
				$par["html"] = $matches[1];
				if (ereg ("CLASS=\"literal\"", $ar[$i+4]))
				{
					preg_match ("/>([^<]+)/", $ar[$i+5], $matches);
				}
				else if ($ar[$i+2] == $ar[$i+4])
				{
					preg_match ("/>([^<]+)/", $ar[$i+7], $matches);
				}
				else
				{
					preg_match ("/>([^<]+)/", $ar[$i+3], $matches);
				}
				$par["title"] = $matches[1];
?>
			<LI> <OBJECT type="text/sitemap">
				<param name="Name" value="<?php echo $par["title"]; ?>">
				<param name="Local" value="html\<?php echo $par["html"]; ?>">
				</OBJECT>
<?php
			}
		}
?>
		</UL>
<?php
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
	for ($i = 0; $i < count ($index_a); $i++)
	{
/* Chapters */
		if (ereg (">[IVX]+\.\ <A", $index_a[$i]) && !ereg ("HREF=\"ref\.[a-z]+\.html", $index_a[$i+1]))
		{
			$new_list = 1;
			if ($not_closed == 1)
			{
?>
	</UL>
<?php
			}
			preg_match ("/>([IVX]+)\. <A/", $index_a[$i], $matches);
			$chapter["nr"] = $matches[1];
			preg_match ("/HREF=\"([a-z-]+\.html)(\#[a-z]+)?\"/", $index_a[$i+1], $matches);
			$chapter["html"] = $matches[1];
			preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
			$chapter["title"] = $matches[1];
?>
	<LI> <OBJECT type="text/sitemap">
		<param name="Name" value="<?php echo $chapter["title"]; ?>">
		<param name="Local" value="html\<?php echo $chapter["html"]; ?>">
		</OBJECT>
<?php
		}
		else
/* Sub chapters */
//		if (ereg (">[0-9]+\.\ <A", $index_a[$i]) && !ereg ("HREF=\"ref\.[a-z]+\.html", $index_a[$i+1]))
		if (ereg (">([0-9]+|[IVXL]+|[A-Z])\.\ <A", $index_a[$i]))
		{
			if ($new_list == 1) {
				$new_list = 0;
				$not_closed = 1;
?>
	<UL>
<?php
			}
			preg_match ("/>([0-9]+|[IVXL]+|[A-Z])\. <A/", $index_a[$i], $matches);
			$schapter["nr"] = $matches[1];
			preg_match ("/HREF=\"([a-z-]+\.([a-z-]+\.)?html)(\#[a-z]+)?\"/", $index_a[$i+1], $matches);
			$schapter["html"] = $matches[1];
			preg_match ("/>([^<]+)/", $index_a[$i+2], $matches);
			$schapter["title"] = $matches[1];
?>
		<LI> <OBJECT type="text/sitemap">
			<param name="Name" value="<?php echo $schapter["title"]; ?>">
			<param name="Local" value="html\<?php echo $schapter["html"]; ?>">
			</OBJECT>
<?php
			DoFile ("html/".$schapter["html"]);
		} 	
	}
?>
		</UL>
	</UL>
</UL>
</BODY></HTML>

