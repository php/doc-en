# todo create different outdir pathes

<script language="php">

set_time_limit(0);
error_reporting(E_ALL);
ob_implicit_flush();

# {{{ convert_file 

function convert_file($dir,$file) {
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
	
	// append filename to function entity list
	$fent= fopen("functions.ent","a");
	fwrite($fent,"<!ENTITY $name.entities SYSTEM '$base/functions.ent'>\n");
	fclose($fent);

	// push dir
	$olddir = getcwd();
	chdir($base);

	// create master documentation file
	$fmaster= fopen("reference.xml","w");

	// current output stream is master file
	$fout = &$fmaster;

	// initialize entity collector
	$entity = array();

	// process input file
	$flag=false;
	$xmlhead="<?xml version='1.0' encoding='iso-8859-1' ?>\n";
	$lineno=0;
 	while ($line = fgets($fin, 4096)) {
		$lineno++;

    $line = ereg_replace("(</?)sect[123456]","\\1section",$line);
    $line = str_replace('../../manual.ced','../../../manual.ced',$line);

		if(strstr($line,("<refentry "))) {
			// start of function description 
			
			// extract id 
			ereg("id=['\"](.*)['\"]",$line,$matches);
			$id=str_replace("_","-",$matches[1]);
			$id=ereg_replace("^function\.","",$id);

			// register entity
			$entity[]="&$base.$id;";

			// open new output stream for this function
			$fslave=fopen("functions/$id.xml","w");
			$fout=&$fslave;
			fwrite($fout,$xmlhead);
			fwrite($fout,$line);
		} else if(strstr($line,("<partintro"))) {
      fwrite($fout,preg_replace("/<partintro(.*?)>/","<section\\1><title>Introduction</title>\n",$line));
		} else if(strstr($line,("</partintro>"))) {
      fwrite($fout,"</section>\n");
		} else if(strstr($line,("<funcsynopsis>"))) {
			
			$xml=$xmlhead.$line;
			do {
				$line=fgets($fin,4096);
				$xml.=$line;
			} while(!strstr($line,("</funcsynopsis>")));
      $result = $xml;
			if(is_string($result))
				fwrite($fout,strstr($result,"\n"));
			else {
				echo $xml; 
			}
		} else if (strstr($line,("<?xml"))&&($lineno==1)) {
			$xmlhead=$line;
			fwrite($fout,$line);
		} else if (strstr($line,("</refentry>"))) {
			// end of function description
			
			// close output stream and switch 
			fwrite($fout,$line);
			if(@is_resource($fslave)) {
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
			}
		} else if (strstr($line,("<reference "))) {
      fwrite($fout,str_replace("<reference","<section role='reference' ",$line));
		} else if (strstr($line,("</reference>"))) {
			// end of master file

			// write entity file
			sort($entity);

			// generate entity include for entity file
			fwrite($fout,"<section><title>Functions</title>&$name.entities;</section>\n\n");
      fwrite($fout,str_replace("</reference","</section",$line));
		} else {
			// default -> just copy to current output stream,
      // filter out duplicate blank lines
			if(trim($line)) {
				fwrite($fout,$line);				
				$flag=false;
			} else {
				if(!$flag)
					fwrite($fout,$line);
				$flag=true; 
			}
		}
	}

	fclose($fmaster); 
	// pop dir
	chdir($olddir);

	// close input stream
	fclose($fin);
}

# }}} 

# {{{ convert_dir

// convert dir -> search for XML files to convert 
//    and recurse into subdirs
function convert_dir($dirname) {

	if ($dir = opendir($dirname)) {

		// for each file in dir
		while (($file = readdir($dir)) !== false) {			

			if($file{0}==".") continue; // ignore hidden files
			if($file=="CVS") continue;  // ignore CVS information

			if(is_dir("$dirname/$file")) { // is directory?
				if(!strstr("$dirname/","/reference/")!==false) {
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

</script>
