<?php

$XML_REF_ROOT = "../../en/reference/";
$FUNCTIONS    = array();

if ($dh = @opendir($XML_REF_ROOT)) {
    while (($file = readdir($dh)) !== FALSE) {
        if (is_dir($XML_REF_ROOT . $file) && !in_array($file, array(".", "..", "CVS"))) {
            get_function_files($XML_REF_ROOT . $file);
        }
    }
    closedir($dh);
} else {
    die("Unable to find phpdoc XML files");
}

sort($FUNCTIONS);
fwrite(fopen("funclist.txt", "w"), implode("\n", $FUNCTIONS)."\n");

function get_function_files($dir) {
    global $FUNCTIONS;
    if ($dh = @opendir($dir . "/functions")) {
        while (($file = readdir($dh)) !== FALSE) {
            if (fnmatch("*.xml", $file)) {
                $FUNCTIONS[] = str_replace(array(".xml", "-"), array("", "_"), $file);
            }
        }
        closedir($dh);
    } else {
        die("Unable to find phpdoc XML files in $dir folder");
    }
}

?>
