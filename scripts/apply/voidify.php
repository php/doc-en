<?php

function apply($input)
{
	$lines = explode("\n",$input);
	foreach ($lines as $nr => $line) {
		if (ereg("^([ \t]*)<paramdef>void</paramdef>",$line,$regs) ||
		    ereg("^([ \t]*)<void></void>",$line,$regs)) {

			$lines[$nr] = "$regs[1]<void/>";
			echo "-$line\n+$lines[$nr]\n";
		}
	}
	return implode("\n",$lines);
}
