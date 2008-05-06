<?php
//*-- The PHPDOC Online XML Editing Tool 
//*-- Developed by Salah Faya visualmind(@)php.net 
//*-- Version 1.0 - essentially developed for Arabic Translation of PHP Manual
//*-- Now updated to work with all phpdoc translations

//--- This file is the frameset 

require 'base.php';
$user = sessionCheck();

?>
<frameset cols="200,*">
 <frame name=listingframe src=cvslist.php>
 <frame name=fileframe src=intro.php>
</frameset>
