<?php

// For use in process.php

// true,false,null -> entities

function apply($input)
{
	$from = '/([^a-zA-Z$._-])(true|false|null)([^a-zA-Z$_-])/i';
	$to = "\\1&\\2;\\3";

	$lines = explode("\n",$input);

	$active = TRUE;

	foreach ($lines as $nr => $line)
	{
		$active = $active && !ereg('<programlisting',$line);
		$active = $active ||  ereg('</programlisting',$line);
		if ($active)
			$lines[$nr] = preg_replace( $from , $to , $line );
		
	}

	$output = implode("\n",$lines);

	// lowercase the entities:
	$output = eregi_replace('&true;','&true;',$output);
	$output = eregi_replace('&false;','&false;',$output);
	$output = eregi_replace('&null;','&null;',$output);

	$from = '/(<constant>)?(<literal>)?&(true|false|null);(<\/constant>)?(<\/literal>)?/';
	$to = "&\\3;";
	

	$output = preg_replace($from,$to,$output);


	$output = ereg_replace('<type>&null;</type>','<type>null</type>',$output);

	return $output;

}
