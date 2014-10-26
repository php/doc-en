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
   <simpara>
    If you disregard the above warning, please also note that the IANA
    timezone database that provides PHP's timezone support uses POSIX style
    signs, which results in the <literal>Etc/GMT+n</literal> and
    <literal>Etc/GMT-n</literal> time zones being reversed from common usage.
   </simpara>
   <simpara>
    For example, the time zone 8 hours ahead of GMT that is used in China and
    Western Australia (among other places) is actually
    <literal>Etc/GMT-8</literal> in this database, not
    <literal>Etc/GMT+8</literal> as you would normally expect.
   </simpara>
   <simpara>
    Once again, it is strongly recommended that you use the correct time zone
    for your location, such as <literal>Asia/Shanghai</literal> or
    <literal>Australia/Perth</literal> for the above examples.
   </simpara>
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

