<?php

/* 
   This file is part of the Windows Compiled HTML Help
   Manual Generator of the PHP Documentation project.
   
   This code splits up the notes file to be easily
   processeable by the notes CHM generator script.
*/

// Check for previous run
if (@is_dir("$NOTES_SRC/0")) {
    echo "\n> Previous user note split detected, skipping\n";
}

// We have no splitted notes files, do it now
else {

    // Open all notes source file for reading
    $fp = @fopen("all", "r");
    if (!$fp) { die("ERROR: No all notes file present"); }
    
    // Read through the file, and write individual files
    while (!feof($fp)) {
        $line = chop(fgets($fp,8096));
        if ($line == "") continue;
        
        // Get data from one line
        list($id,$sect,$rate,$ts,$user,$note) = explode("|",$line);
        $hash = substr(md5($sect),0,16);
        
        // Create dir if nonexistent
        if (!@is_dir("$NOTES_SRC/" . $hash[0])) {
            mkdir("$NOTES_SRC/" . $hash[0], 0700);
        }
        
        // Append line to appropriate file
        $nf = fopen("$NOTES_SRC/" . $hash[0] . "/$hash", "a");
        fwrite($nf, $line . "\n");
        fclose($nf);
    }
    
    // Close all notes file
    fclose($fp);

}

?>