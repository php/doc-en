<?php

# add cvs Revision: tags where missing

function apply($input) {
	if( strstr($input, '$Revision: ')) {
		return false;
	}

	list($head, $rest) = explode("\n", $input, 2);

	return $head."\n".'<!-- $Revision$ -->'."\n".$rest;
}

?>
 
