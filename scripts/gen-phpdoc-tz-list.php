<?php
	$groupedList = array();
	$aliasList = array(
		'Brazil' => 'Others',
		'Canada' => 'Others',
		'Chile' => 'Others',
		'Etc' => 'Others',
		'Mexico' => 'Others',
		'US' => 'Others',
	);
	$list = timezone_identifiers_list(DateTimeZone::ALL_WITH_BC);

	foreach ($list as $element) {
		if (preg_match('@^([^/]*)/(.*)@', $element, $m)) {
			$group = $m[1];
		} else {
			$group = 'Others';
		}
		if (isset($aliasList[$group])) {
			$group = $aliasList[$group];
		}
		$groupedList[$group][] = $element;
	}
    ksort($groupedList);
    $others = $groupedList['Others'];
    unset($groupedList['Others']);
    $groupedList['Others'] = $others;
?>
<?php echo '<?'; ?>xml version="1.0" encoding="UTF-8"?>
<!-- $Revision$ -->
<!-- AUTO GENERATED, DO NOT TRANSLATE OR MODIFY BY HAND -->

<appendix xml:id="timezones" xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink">
 &date.timezone.intro.title;
 &date.timezone.intro;
<?php
    if (function_exists('timezone_version_get')) {
?>

 <note>
  <simpara>
   &date.timezone.dbversion; <?php echo timezone_version_get(); ?>.
  </simpara>
 </note>
<?php
    }

    foreach ($groupedList as $group => $zones) { 
        $m = count($zones) > 4 ? 5 : count($zones); ?>

 <sect1 xml:id="timezones.<?php echo strtolower($group); ?>">
  <title><?php echo $group; ?></title>
  <table>
   <title><?php echo $group; ?></title>
   <tgroup cols="<?php echo $m; ?>">
    <tbody>
<?php
    $c = 0;
    foreach($zones as $zone) {
        if ($c % $m == 0) {
            echo "     <row>\n";
        }
        $c++;
        echo "      <entry>{$zone}</entry>\n";
        if ($c % $m == 0) {
            echo "     </row>\n";
        }
    }
    if ($c % $m != 0) {
        while($c++ % $m != 0) {
            echo "      <entry></entry>\n";
        }
        echo "     </row>\n";
    }
?>
    </tbody>
   </tgroup>
  </table>
<?php if ( $group == 'Others' ) { ?>
  <warning>
   &date.timezone.bc;
  </warning>
<?php } ?>
 </sect1>
<?php } ?>
</appendix>

<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil
sgml-default-dtd-file:"../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=php
vi: ts=4 sw=1
-->
