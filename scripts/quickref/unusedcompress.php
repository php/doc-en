<?

$tocomp=implode("", file("prefixcompressed.txt"));
$tocomp=str_replace("\n", "", $tocomp);

$result="";
$allchars="";
for ($a=32; $a<125; $a++)
	if (chr($a)!="'" && chr($a)!="\\" && $a!=127)
		$allchars.=chr($a);
$acl=strlen($allchars);

function GetUnused($text)
{
	global $allchars, $acl;
	for ($a=0; $a<$acl; $a++)
		if (strpos($text, $allchars[$a])===false)
			return $allchars[$a];
	return "";
}


while (true) {
	$replacewith=GetUnused($tocomp);
	if (!strlen($replacewith))
		break;

	$sc=strlen($tocomp);

	$already=array();
	$best=0;
	$counter=array();

	for ($len=2; $len<8; $len++) {
		for ($pos=0; $pos<=($sc-$len); $pos++) {
			$wh=substr($tocomp, $pos, $len);
			if (!isset($counter[$wh])) {
				$counter[$wh]=-3;
			} else {
				$counter[$wh]+=$len-1;
			}
		}
	}

	$best=max($counter);

	if ($best>0) {
		$bestchars=array_search($best, $counter);
		$tocomp=str_replace($bestchars, $replacewith, $tocomp);
		if ($result) 
			$result=$replacewith.$bestchars."}".$result;
		else
			$result=$replacewith.$bestchars;
	} else {
		break;
	}
}

fwrite(fopen("compressed.txt", "w"), $tocomp);
fwrite(fopen("compkey.txt", "w"), $result);

?>
