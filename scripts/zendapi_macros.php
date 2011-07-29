<?php
$overwrite = false;

$zend_include_dir = "../../php-src/Zend";

$zend_include_files = array(
                            "zend.h", 
                            "zend_API.h", 
                            "zend_builtin_functions.h",
                            "zend_compile.h",
                            "zend_constants.h",
                            "zend_exceptions.h",
                            "zend_execute.h",
                            "zend_hash.h", 
                            "zend_highlight.h",
                            "zend_interfaces.h",
                            "zend_ini.h",
                            "zend_list.h", 
                            "zend_modules.h",
                            "zend_objects.h",
                            "zend_object_handlers.h",
                            "zend_objects_API.h",
                            "zend_qsort.h",
                            "zend_stream.h",
                            "zend_strtod.h",
                            "zend_unicode.h",
                            "zend_variables.h",
                            "../TSRM/TSRM.h",
                            "../TSRM/tsrm_virtual_cwd.h",
                            );

$output_dirs = array("../en/internals/zendapi/macros" => array("ZEND_", "Z_", "RETURN_"), 
                     "../en/internals/tsrm/macros" => array("VCWD_"));

foreach ($zend_include_files as $infile) {
  echo "processing $zend_include_dir/$infile\n";
  
  $in = fopen("$zend_include_dir/$infile", "r");
  
  if (!$in) {
    die("can't open $zend_include_dir/$infile");
  }
  
  // loop over all lines in the file
  while (!feof($in)) {
    $line = trim(fgets($in));

    // now check for all known macro prefixes
    foreach ($output_dirs as $output_dir => $macro_prefixes) {
      foreach ($macro_prefixes as $prefix) {
        // does this line match a macro definition?
        if (preg_match("|#define\\s*($prefix\\w+)\\((.*)\\)|U", $line, $matches)) {
          // get macro name and parameter list from the matches
          $macro  = $matches[1];
          $params = preg_split('|,\s+|', $matches[2]);

          // path to output file
          $outfile = $output_dir."/".$macro.".xml";

          // do not overwrite existing files unless specified
          if ($overwrite || !file_exists($outfile)) {
            echo "writing $outfile\n";
            // now write the template file to phpdoc/en/internals/zendapi/macros
            ob_start();
          
            echo '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
          
            // take revision from existing file if any, else it is 1.1
            if (!$overwrite || !file_exists($outfile)) {
              echo "<!-- $"."Revision: 1.1 $ -->\n";
            } else {
              foreach (file($outfile) as $line) {
                if (strstr($line, 'Revision: ')) {
                  echo $line;
                  break;
                }
              }
            }
?>
<refentry id="zend-macro.<?php echo strtolower(str_replace("_", "-", $macro)); ?>">
 <refnamediv>
  <refname><?php echo $macro; ?></refname>
  <refpurpose>...</refpurpose>
 </refnamediv>

 <refsect1 role="description">
  &reftitle.description;          
  <literallayout>#include &lt;<?php echo basename($infile); ?>&gt;</literallayout>
  <methodsynopsis>
   <type>???</type><methodname><?php echo $macro; ?></methodname>
<?php
            foreach($params as $param) {
              echo "    <methodparam><type>???</type><parameter>$param</parameter></methodparam>\n";
            }
?>
  </methodsynopsis>
  <para>
   ...
  </para>
 </refsect1>

 <refsect1 role="parameters">
  &reftitle.parameters;
  <para>
   <variablelist>
<?php
            foreach($params as $param) {
?>
    <varlistentry>
     <term><parameter><?php echo $param; ?></parameter></term>
     <listitem>
      <para>
       ...
      </para>
     </listitem>
    </varlistentry>
<?php
            }
?>
   </variablelist>
  </para>
 </refsect1>

 <refsect1 role="returnvalues">
  &reftitle.returnvalues;
  <para>
   ...
  </para>
 </refsect1>

</refentry>

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
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
<?php
       
            file_put_contents($outfile, ob_get_clean());
          }
        }
      }
    }
  }
  
}
?>
