<?php

# convert function refnames to lowercase

function apply($input) {
	$flag = false;

	if( ereg("<refname>([^<]*)</refname>", $input, $matches)) {
		$name = $matches[1];
		$lower = strtolower($name);
		if($lower != $name) {
			$input = str_replace("<refname>$name</refname>", "<refname>$lower</refname>", $input);
			$flag = true;
		}
	}

	return $flag ? $input : false ;
}

?>
