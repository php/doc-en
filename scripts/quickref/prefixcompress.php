<?

$funcs=file("funclist.txt");
foreach($funcs as $key => $val)
	$funcs[$key]=trim($val);

$result=DoCompress($funcs);

$outfile=fopen("prefixcompressed.txt", "w");
fwrite($outfile, DoCompress($funcs));

$cache=array();

function DoCompress($list, $level=0)
{
	global $cache;
	$key=implode(",",$list);
	if (isset($cache[$key]))
		return $cache[$key];

	$result="";

	while (sizeof($list)) {
		if ($result && substr($result,-1)!=')' && substr($result,-1)!=']') $result.=",";
		if (sizeof($list)==1) {
			$result.=$list[0];
			return ($cache[$key]=$result);
		}
		if (sizeof($list)==2) {
			if (substr($list[1],0,strlen($list[0]))==$list[0]) {
				$result.=$list[0]."[".substr($list[1],strlen($list[0]))."]";
				return ($cache[$key]=$result);
			} else {
				if (substr($list[0],0,2)!=substr($list[1],0,2)) {
					$result.=$list[0].",".$list[1];
				} else {
					$len=2;
					while ($len<strlen($list[0]) && substr($list[0],0,$len+1)==substr($list[1],0,$len+1)) {
						$len++;
					}
					$result.=substr($list[0],0,$len)."(".substr($list[0],$len).",".substr($list[1],$len).")";
				}
				return ($cache[$key]=$result);
			}
		}
		if ($list[0][0]!=$list[1][0] || ($list[0][1]!=$list[1][1] && $list[2][0]!=$list[0][0])) {
			$result.=array_shift($list);
			continue;
		}

		$bestsave=0;
		$bestremain=$list;
		$bestresult=array_shift($bestremain);

		if (substr($list[1],0,strlen($list[0]))==$list[0]) {
			$tmplist=$list;
			$prefix=array_shift($tmplist);
			$subs=array();
			while (substr($tmplist[0],0,strlen($prefix))==$prefix) {
				$subs[]=substr(array_shift($tmplist),strlen($prefix));
			}
			$tmpresult=$prefix."[";
			$tmpresult.=DoCompress($subs);
			$tmpresult.="]";
			$bestsave=sizeof($subs)*strlen($prefix)-1;
			if (sizeof($tmplist)) $bestsave++;
			$bestremain=$tmplist;
			$bestresult=$tmpresult;
		}
		for ($len=strlen($list[0])-1; $len>=1; $len--) {
			$prefix=substr($list[0],0,$len);
			$count=0;
			while ($count<sizeof($list) && substr($list[$count],0,$len)==$prefix)
				$count++;
			$save=($count-1)*$len-2;
			if ($count<sizeof($list))
				$save++;
			if ($save>$bestsave) {
				$bestsave=$save;
				$tmplist=$list;
				$subs=array();
				for ($a=0; $a<$count; $a++)
					$subs[]=substr(array_shift($tmplist),$len);
				$bestremain=$tmplist;
				$bestresult=$prefix."(".DoCompress($subs).")";
			}
		}
		$result.=$bestresult;
		$list=$bestremain;
	}
	return ($cache[$key]=$result);
}

?>
