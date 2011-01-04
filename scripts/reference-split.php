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
  | Authors:    Hartmut Holzgraefe <hholzgra@php.net>                    |
  +----------------------------------------------------------------------+
 
  $Id$
*/

set_time_limit(0);
error_reporting(E_ALL);
ob_implicit_flush();

if(php_sapi_name()!="cli") chdir("..");

$en_revs = array();

# {{{ cvs stuff

  // compare two revision numbter arrays
  function rev_cmp($a1,$a2) {
    foreach($a1 as $key=>$val) {
      if($a1[$key]>$a2[$key]) return 1;
      if($a1[$key]<$a2[$key]) return -1;
    }
    if(count($a1)>count($a2)) return 1;
    if(count($a1)<count($a2)) return -1;
    return 0;
  }

  // get max. revision for a region in a cvs file 
  // (witch simple data caching)
  function cvs_max_rev($filename,$start,$end) {
    static $lastfile = "";
    static $array = array();
    
    if($filename!=$lastfile) {
      $cmd="/usr/bin/cvs annotate $filename 2>/dev/null";
      $fp=popen($cmd,"r");
      if(!$fp) return false;

      $n=0;
      $array = array();
      $lastfile = $filename;

      while(!feof($fp)) {
        $line = fgets($fp);
        if(empty($line)) continue;
        $tokens=explode(" ",$line);
        $array[++$n]=explode(".",$tokens[0]);
      }
      pclose($fp);
    }

    $max=array();
    for($n=$start;$n<=$end;$n++)
      if(rev_cmp($max,$array[$n])) $max = $array[$n];
    return $max;
  }
# }}}

# {{{ convert_file 

function convert_file($dir,$file) {
  global $en_revs;

	echo "convert $dir $file\n";

	// open input stream
	$fin = fopen("$dir/$file","r");

	// get extension basename 
	$name = str_replace(".xml","",$file); 
	$name = str_replace("_","-",$name);

	// get language from path
	$parts=explode("/",$dir);
	$lang=$parts[1];

	// create directories for generated extension files 
	$base="./$lang/reference/$name";
	if(!file_exists("./$lang/reference")) mkdir("./$lang/reference",0777);
	if(file_exists($base)) exec("rm -rf $base"); // cleanup
	mkdir($base,0777);
	mkdir("$base/functions",0777);

	// create master documentation file
	$fmaster= fopen("$base/reference.xml","w");

	// current output stream is master file
	$fout = &$fmaster;

	// initialize entity collector
	$entity = array();

	// process input file
	$lineno = 0;
	$lastline_empty = false;
	$xmlhead = "<?xml version='1.0' encoding='iso-8859-1' ?>\n"; //default
  $en_revision = false;
  $maintainer = "";
  $trans_status = "";

 	while ($line = fgets($fin)) {
		$lineno++;

    // convert numbered sections to generic ones, prep. for hierachical ref
    $line = ereg_replace("(</?)sect[123456]","\\1section",$line);

    // file will move one level down, so path to emacs dtd-file needs one more ..
    $line = str_replace('../../manual.ced','../../../manual.ced',$line);

		if (strstr($line,("<?xml"))&&($lineno==1)) { // remember xml header
			$xmlhead=$line;
			fwrite($fout,$line);
    } elseif (strstr($line,"<!-- EN-Revision:")) {
      $array = explode(" ",$line);
      foreach($array as $key => $value) {
        if($value=="EN-Revision:") $en_revision  = explode(".",$array[$key+1]);
        if($value=="Maintainer:")  $maintainer   = $array[$key+1];
        if($value=="Status:")      $trans_status = $array[$key+1];
      }
		} elseif(strstr($line,("<refentry "))) { // start of function description 
			// extract id 
			ereg("id=['\"](.*)['\"]",$line,$matches);
			$id=str_replace("_","-",$matches[1]);
			$id=ereg_replace("^function\.","",$id);

			// register entity
			$entity[]=$id;

			// open new output stream for this function
			$fslave=fopen("$base/functions/$id.xml","w");
			$fout=&$fslave;

      // xml header
			fwrite($fout,$xmlhead);

      // start collecting stuff
		  $block = $line;
      $blockstart=$lineno;
		} else if (strstr($line,("</refentry>"))) {
			// end of function description
			
			// close output stream and switch 
			if(!isset($block)) {
        fwrite($fout,$line);
      }else{
        $cvs_rev = cvs_max_rev("$dir/$file",$blockstart,$lineno);
        fwrite($fout,"<!-- splitted from $dir/$file, last change in rev ".join(".",$cvs_rev)." -->\n");
        if(!isset($en_revs[$id])) {
          $en_revs[$id] = $cvs_rev;
        } else {
          fwrite($fout,"<!-- last change to '$id' in en/ tree in rev ".join(".",$en_revs[$id])." -->\n");
        }
        // revcheck header
        if(is_array($en_revision)) {
          if(rev_cmp($en_revision,$en_revs[$id])>=0) { // new enough
            fwrite($fout,"<!-- EN-Revision: 1.1 Maintainer: $maintainer Status: $trans_status -->\n");
          } else {
            fwrite($fout,"<!-- EN-Revision: 0.0 Maintainer: $maintainer Status: $trans_status -->\n");
          }
          fwrite($fout,"<!-- OLD-Revision: ".join(".",$en_revision)."/EN.".join(".",$en_revs[$id])." -->\n");
        }
        fwrite($fout,$block.$line);
				fwrite($fout,'
<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil
sgml-default-dtd-file:"../../../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
');				
				fclose($fslave);
				$fout = &$fmaster;
        unset($block);
			}
		} else if (strstr($line,("</reference>"))) {
			// end of master file

			// write entity file
			sort($entity);
      $fentities = fopen("$base/functions.xml","w");
      foreach($entity as $key => $value) {
				fputs($fentities,"&reference.$name.functions.".basename($value).";\n");
			}
			fclose($fentities);

			// generate entity include for entity file
			fwrite($fout,"&reference.$name.functions;\n\n");
      fwrite($fout,$line);
		} else {
			// default -> just copy to current output stream,
      // filter out duplicate blank lines
			if(trim($line)) {
        if(isset($block))
          $block .= $line;
        else
          fwrite($fout,$line);				
				$lastline_empty=false;
			} else {
				if(!$lastline_empty) {
          if(isset($block))
            $block .= $line;
          else
            fwrite($fout,$line);				
        }
				$lastline_empty=true; 
			}
		}
	}

	fclose($fmaster); 
	// close input stream
	fclose($fin);
}

# }}} 

# {{{ convert_dir

// convert dir -> search for XML files to convert 
//    and recurse into subdirs
function convert_dir($dirname) {

  if(@is_dir("$dirname/en")) {
    convert_dir("$dirname/en");
  }

	if ($dir = opendir($dirname)) {

		// for each file in dir
		while (($file = readdir($dir)) !== false) {			

			if($file{0}==".") continue; // ignore hidden files
			if($file=="CVS") continue;  // ignore CVS information

			if(is_dir("$dirname/$file")) { // is directory?
				if($file!="en" && $file!="reference") {
          convert_dir("$dirname/$file"); // recurse if not 'reference'
        }
			} else if(ereg("xml$",$file)) { // is XML file?
				if(strpos("$dirname/","/functions/")>0) {
					convert_file($dirname,$file); // process if in 'functions'
				}
			}

		}

		closedir($dir);
	}
}

# }}}


// convert the current directory
convert_dir(".");

?>
