<?php
/*

Scan directory tree against original language tree and define 
file entities for translated files where present with original 
untranslated files as fallback where no translated version 
exists

*/
ob_end_clean();
ob_implicit_flush();

$base_dir  = abs_path($argv[$argc-2]);
if(php_sapi_name()!="cli") $base_dir=ereg_replace("/scripts$","",$base_dir);

$orig_dir  = $base_dir."/en";
$trans_dir = $base_dir."/".$argv[$argc-1];

process($orig_dir,$trans_dir,$orig_dir);

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

function process($work_dir, $trans_dir, $orig_dir) {
	$trans_path = str_replace($orig_dir,$trans_dir,$work_dir);
	$dh = opendir($work_dir);
	if(!$dh) return false;
	while(false !== ($file = readdir($dh))) {
		if($file=="."||$file==".."||$file=="CVS") continue;
		if(is_dir($work_dir."/".$file)) process($work_dir."/".$file, $trans_dir, $orig_dir);
		if(ereg("\\.xml$",$file)) {
			$name = str_replace("$orig_dir/","",$work_dir."/".ereg_replace("\\.xml$","",$file));
			$name = str_replace("/",".",$name);
			$name = str_replace("_","-",$name);
			if(file_exists("$trans_path/$file")) {
				$path= "$trans_path/$file";
			} else {
				$path= "$work_dir/$file";
			}
			echo sprintf("<!ENTITY %-40s SYSTEM '$path'>\n",$name);
		}
	}
	closedir($dh);
}
?>
