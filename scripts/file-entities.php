<?php
/*

Scan directory tree against original language tree and define 
file entities for translated files where present with original 
untranslated files as fallback where no translated version 
exists

*/
ob_implicit_flush();
set_time_limit(0);

$base_dir  = str_replace("\\","/",abs_path($argv[$argc-3]));
$base_dir=ereg_replace("/scripts$","",$base_dir);

$orig_dir  = $base_dir."/en";
$trans_dir = $base_dir."/".$argv[$argc-2];

$out_dir = $argv[$argc-1];

$entities = array();
process($orig_dir, $trans_dir, $orig_dir, $entities);

$fp = fopen("$out_dir/entities/file-entities.ent", "a")
  or die("failed to open $out_dir/entities/file-entities.ent for writing");
foreach($entities as $entity) {
	fputs($fp, "$entity\n");
}
fclose($fp);

exit;



function abs_path($path) {
	if($path{0} == '/') return $path;

	$absdirs = explode("/",getcwd());
	$dirs    = explode("/",$path);

	foreach($dirs as $dir) {
		if(empty($dir) or $dir==".") continue;
		else if($dir=="..") array_pop($absdirs);
		else array_push($absdirs,$dir);
	}

	return join("/",$absdirs);
}

function process($work_dir, $trans_dir, $orig_dir, &$entities) {

	$trans_path = str_replace($orig_dir,$trans_dir,$work_dir);
	$dh = opendir($work_dir);
	if(!$dh) return false;

	if(ereg("/reference/.*/functions$", $work_dir)) {
		$function_entities = array();
		$functions_file = "$work_dir.xml";

		$functions_file_entity = str_replace("$orig_dir/","",$work_dir);
		$functions_file_entity = str_replace("/",".",$functions_file_entity);
		$functions_file_entity = str_replace("_","-",$functions_file_entity);

		$entities[] = sprintf("<!ENTITY %-40s SYSTEM '%s'>\n",$functions_file_entity,$functions_file);
	}

	while(false !== ($file = readdir($dh))) {

		if($file{0} == ".") 
			continue;

		if(is_dir($work_dir."/".$file)) {
			if($file == "CVS") continue;
			process($work_dir."/".$file, $trans_dir, $orig_dir, $entities);
		}

		if(ereg("\\.xml$",$file)) {
      $name = str_replace("$orig_dir/","",$work_dir."/".ereg_replace("\\.xml$","",$file));
			$name = str_replace("/",".",$name);
			$name = str_replace("_","-",$name);

			if(isset($function_entities)) {
				$function_entities[] = "&$name;";
			}
			
      // new: special treatment for function reference entities if splitted version available
      if(strstr($work_dir,"/functions")) {
        $splitfile = str_replace(".xml","/reference.xml",$file);
        $splitpath = str_replace("/functions","/reference",$trans_path) . "/" . $splitfile;
        if(file_exists($splitpath)) {
          $entities[] = sprintf("<!ENTITY %-40s SYSTEM '%s'>\n",$name,$splitpath);
          continue;
        } 
        $splitpath = str_replace("/functions","/reference",$trans_path) . "/" . $splitfile;
        if(file_exists($splitpath)) {
          $entities[] = sprintf("<!ENTITY %-40s SYSTEM '%s'>\n",$name,$splitpath);
          continue;
        } 
      }
  
      if(file_exists("$trans_path/$file")) {
				$path= "$trans_path/$file";
			} else {
				$path= "$work_dir/$file";
			}
			$entities[] = sprintf("<!ENTITY %-40s SYSTEM '$path'>\n",$name);
		}
	}
	closedir($dh);

	if(isset($function_entities)) {
		sort($function_entities);
		$fp = fopen($functions_file, "w");
		foreach($function_entities as $entity) {
			fputs($fp, "$entity\n");
		}
		fclose($fp);
	}

  // now find all files available in the translation but not the original
  if($orig_dir != $trans_dir && file_exists($trans_path) && is_dir($trans_path)) {
    $dh = @opendir($trans_path);
    if($dh) {
      while(false !== ($file = readdir($dh))) {
        if($file{0}=="." || $file=="CVS") continue;
        if(is_dir($trans_path."/".$file)) continue;
        if(ereg("\\.xml$",$file)) {
          $name = str_replace("$orig_dir/","",$work_dir."/".ereg_replace("\\.xml$","",$file));
          $name = str_replace("/",".",$name);
          $name = str_replace("_","-",$name);
          
          if(!file_exists("$work_dir/$file")) {
            $path= "$trans_path/$file";
            $entities[] = sprintf("<!ENTITY %-40s SYSTEM '$path'>\n",$name);
          }
        }
      }
      closedir($dh);
    } 
  }

}
?>
