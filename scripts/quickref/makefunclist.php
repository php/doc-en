<?

$lines=file("manual_en_contents.txt");
$funcs=array();

foreach($lines as $line) {
	$line=trim($line);
	if (substr($line, -4)!=".php") continue;
	if (substr($line, 0, 9)=="function.")
		$funcs[]=str_replace("-", "_", substr($line, 9, -4));
}

sort($funcs);
fwrite(fopen("funclist.txt", "w"), implode("\n", $funcs)."\n");

?>
