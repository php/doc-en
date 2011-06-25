<?php
function list_files($basedir, $extensions = array('xml')) {
	
	if (!is_dir($basedir)) {
		return false;
	}
	
	$files = array();
	foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basedir)) as $file) {
		
		if (!$file->isFile()) {
			continue;
		}

		$filepath = $file->getPathname();
		
		if (!in_array(pathinfo($filepath, PATHINFO_EXTENSION), $extensions)) {
			continue;
		}
		
		$files[] = $filepath;
	}
	return $files;
}

