<?php
	$columns = 4;
	$abbrevColumns = 8;
	$groupedList = array();
	$aliasList = array(
		'Brazil' => 'Others',
		'Canada' => 'Others',
		'Chile' => 'Others',
		'Etc' => 'Others',
		'Mexico' => 'Others',
		'US' => 'Others',
	);
	$nonBcList = timezone_identifiers_list();
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
		if ( !in_array($element, $nonBcList)) {
			$group = 'Others';
		}
		$groupedList[$group][] = $element;
	}
	ksort($groupedList);
	$others = $groupedList['Others'];
	unset($groupedList['Others']);
	$groupedList['Others'] = $others;
	$groupedList['Abbreviations'] = array_keys(timezone_abbreviations_list());
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
        $groupColumns = ($group == 'Abbreviations') ? $abbrevColumns : $columns;
        $m = count($zones) > ($groupColumns-1) ? $groupColumns : count($zones); ?>

 <sect1 xml:id="timezones.<?php echo strtolower($group); ?>">
  <title><?php echo '&date.timezone.' . strtolower($group) . ';'; ?></title>
  <table>
   <title><?php echo '&date.timezone.' . strtolower($group) . ';'; ?></title>
   <tgroup cols="<?php echo $m; ?>">
    <tbody>
<?php
    $c = 0;
    foreach($zones as $zone) {
        if ($c % $m == 0) {
            echo "     <row>", PHP_EOL;
        }
        $c++;
        echo "      <entry>{$zone}</entry>", PHP_EOL;
        if ($c % $m == 0) {
            echo "     </row>", PHP_EOL;
        }
    }
    if ($c % $m != 0) {
        while($c++ % $m != 0) {
            echo "      <entry></entry>", PHP_EOL;
        }
        echo "     </row>", PHP_EOL;
    }
?>
    </tbody>
   </tgroup>
  </table>
<?php if ( $group == 'Others' ) { ?>
  <warning>
   &date.timezone.bc;
  </warning>
  <warning>
   &date.timezone.posix-signs;
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
sgml-default-dtd-file:"~/.phpdoc/manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=php
vi: ts=4 sw=1
-->

