<?php
$zend_include_dir = "../../php-src/Zend";

$zend_include_files = array("zend_API.h", "zend_objects_API.h");

$functions_dir = "../en/internals/zendapi/functions/";

foreach ($zend_include_files as $infile) {
    echo "processing $zend_include_dir/$infile\n";

    $in = fopen("$zend_include_dir/$infile", "r");

    if (!$in) {
        die("can't open $zend_include_dir/$infile");
    }

    // loop over all lines in the file
    while (!feof($in)) {
        // TODO a prototype may span more than one line?
        $line = trim(fgets($in));

        // we look for prototypes marked with ZEND_API 
        // TODO prototypes may be indented by whitespace?
        if (!strncmp("ZEND_API", $line, 8)) {

            // parse prototypes, step #1
            if (preg_match('|^ZEND_API\s+(\S+)\s+(\S+)\((.*)\);$|', $line, $matches)) {

                $return_type = $matches[1];
                $function    = $matches[2];

                // the pointer '*' is usually next to the function name, not the type 
                // TODO what if there is whitespace on both sides of the '*'?
                if ($function{0} == '*') {
                    $return_type.= "*";
                    $function = substr($function, 1);
                }

                echo "  $function\n";

                // the parameters are spearated by commas
                // TODO find a better way to handle TSRMLS_D and TSRMLS_DC
                // TODO handle ...
                $params = array();
                foreach (explode(",", trim($matches[3])) as $param) {
                    $tokens = preg_split("/\s+/", trim($param));
                    $type   = array_shift($tokens);
                    $name   = implode(" ", $tokens);
                    if (empty($name)) {
                        $params[] = $type;
                    } else {
                        if ($name{0} == '*') {
                            $type.= "*";
                            $name = substr($name, 1);
                        }
                        $params[$type] = $name;
                    }
                }


                // now write the template file to phpdoc/en/internals/zendapi/functions
                ob_start();
                
                echo '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
                echo "<!-- $"."Revision: 1.1 $ -->\n";

?>
<refentry id="zend-api.<?php echo str_replace("_","-",$function); ?>">
 <refnamediv>
  <refname><?php echo $function; ?></refname>
  <refpurpose>...</refpurpose>
 </refnamediv>

 <refsect1 role="description">
  &reftitle.description;
  <literallayout>#include &lt;<?php echo basename($infile); ?>&gt;</literallayout>
  <methodsynopsis>
   <type><?php echo $return_type; ?></type><methodname><?php echo $function; ?></methodname>
<?php
   foreach($params as $type => $name) {
       if (is_numeric($type)) $type = "";
       echo "    <methodparam><type>$type</type><parameter>$name</parameter></methodparam>\n";
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
   foreach($params as $type => $name) {
       if (is_numeric($type)) $type = "";
?>
    <varlistentry>
     <term><parameter><?php echo $name; ?></parameter></term>
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
sgml-default-dtd-file:"../../../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
<?php
       
       file_put_contents($functions_dir."/".$function.".xml", ob_get_clean());
            }

        }
                                            
    }
}
?>