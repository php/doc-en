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

$api_dir = array("ZEND"=>"../en/internals/zendapi",
                 "TSRM"=>"../en/internals/tsrm",
                 "CWD" =>"../en/internals/tsrm",);


$functions = array();
$wrappers  = array();

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
        if (substr($line, -1) == "\\") { // multiline macro, read one more line
            $line = substr($line, 0, -1) . trim(fgets($in));
        }       
        
        // first we look for prototypes marked with ZEND_API 
        if (preg_match('/^\s*(ZEND|TSRM|CWD)_API\s+(\S+)\s+(\S+)\((.*)\)/U', $line, $matches)) {
            // parse prototypes, step #1
          
            // extract return type and function name 
            $api_type    = $matches[1];
            $return_type = $matches[2];
            $function    = $matches[3];
            $param_list  = $matches[4];

            // the pointer '*' is usually next to the function name, not the type 
            // TODO what if there is whitespace on both sides of the '*'?
            while ($function[0] == '*') {
                $return_type.= "*";
                $function = substr($function, 1);
            }

            // the parameters are spearated by commas
            // TODO find a better way to handle TSRMLS_D and TSRMLS_DC
            // TODO handle ...
            $params = array();
            foreach (explode(",", $param_list) as $param) {
              $new_param = array();
              
              $tokens = preg_split("/\s+/", trim($param));
              $name   = array_pop($tokens);
              if (preg_match("|_DC$|", $name)) {
                $magic = $name;
                $name  = array_pop($tokens);
              } else {
                $magic = "";
              }
              $type   = implode(" ", $tokens);
              
              if (empty($name)) {
                $new_param['type'] = "magic";
                $new_param['name'] = $type;
              } else {
                while ($name[0] == '*') {
                  $type.= "*";
                  $name = substr($name, 1);
                }
                $new_param['type'] = $type;
                $new_param['name'] = $name;
              }
              $params[$name] = $new_param;
              
              if ($magic) {
                $params[$magic] = array("type"=>"magic", "name"=>$magic);
              }
            }
            
            $functions[$function] = array("name"        => $function,
                                          "return_type" => $return_type, 
                                          "params"      => $params,
                                          "api_type"    => $api_type,
                                          "infile"      => $infile
                                          );
        }        

        // next we look for macros that seem to be just wrappers around existing functions
        // TODO catch multiline definitions
        if (preg_match('|^#define\s+(\w+)\((.*)\)\s+(\w+)\(|U', $line, $matches)) {
          $wrapper    = $matches[1];
          $param_list = $matches[2]; 
          $function   = $matches[3];

          $wrappers[$wrapper] = array("function"   => $function, 
                                      "wrapper"    => $wrapper, 
                                      "param_list" => $param_list,
                                      "infile"     => $infile
                                      );
        }
    }
}


do {
  $additions = 0;
  foreach ($wrappers  as $name => $wrapper) {
    if (isset($functions[$wrapper["function"]])) {
      $function = $functions[$wrapper["function"]];
      $params   = array();
      foreach (explode(",", $wrapper["param_list"]) as $param) {
        $param = preg_replace('|\s*_*(\w+)\s*|', '${1}', $param); // trim and strip leading _s
        if (isset($function["params"][$param])) {
          $param_type = $function["params"][$param]["type"];
        } else {
          $param_type = "...";
        }
        $params[$param] = array("type"=>$param_type, "name"=>$param);
      }
      
      $functions[$name] = array("name"        => $name,
                                "return_type" => $function["return_type"], 
                                "params"      => $params,
                                "api_type"    => $function["api_type"],
                                "infile"      => $wrapper["infile"]
                                );
      unset($wrappers[$name]);
      $additions++;
    }
  }
} while ($additions > 0);

foreach ($functions as $name => $function) {
  create_page($name, $function["return_type"], $function["params"], $function["api_type"], $function["infile"]); 
}


function create_page($function, $return_type, $params, $api_type, $infile)
{
  global $overwrite, $api_dir;

  // now generate the doc filename for this function
  $functype = (strtolower($function) == $function) ? "function" : "macro";
  $filename = $api_dir[$api_type]."/".$functype."s/".$function.".xml";
            
  // only proceed it fhe file doesn't exist yet (no overwrites)
  // and do not expose functions staring with '_'
  if (($function[0] != '_') && ($overwrite || !file_exists($filename))) {
    // now write the template file to phpdoc/en/internals/zendapi/functions
    echo "writing $filename\n";
    ob_start();
                
    echo '<?xml version="1.0" encoding="iso-8859-1"?>'."\n";
                
    // take revision from existing file 
    if (!$overwrite || !file_exists($filename)) {
      echo "<!-- $"."Revision: 1.1 $ -->\n";
    } else {
      foreach (file($filename) as $line) {
        if (strstr($line, 'Revision: ')) {
          echo $line;
          break;
        }
      }
    }

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
<?php 
    if ($return_type == "void") {
      echo "   <void/>";
    } else {
      echo "   <type>$return_type</type>";
    }
    echo "<methodname>$function</methodname>";
    if (count($params)) {
      echo "\n";
      foreach($params as $param) {
        echo "    <methodparam><type>$param[type]</type><parameter>$param[name]</parameter></methodparam>\n";
      }  
    } else {
      echo "<void/>\n";
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
     <term><parameter><?php echo $param["name"]; ?></parameter></term>
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
       
    file_put_contents($filename, ob_get_clean());                
  }   
}
?>
