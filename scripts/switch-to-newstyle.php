<?php
/*
+----------------------------------------------------------------------+
| PHP Version 4                                                        |
+----------------------------------------------------------------------+
| Copyright (c) 2005 The PHP Group                                     |
+----------------------------------------------------------------------+
| This source file is subject to version 3.0 of the PHP license,       |
| that is bundled with this package in the file LICENSE, and is        |
| available through the world-wide-web at the following url:           |
| http://www.php.net/license/3_0.txt.                                  |
| If you did not receive a copy of the PHP license and are unable to   |
| obtain it through the world-wide-web, please send a note to          |
| license@php.net so we can mail you a copy immediately.               |
+----------------------------------------------------------------------+
| Authors:    Mehdi Achour <didou@php.net>                             |
+----------------------------------------------------------------------+

$Id$
*/

if (PHP_SAPI !== 'cli') {
    echo "This script is ment to be run under CLI\n";
    exit(1);
}

if ($_SERVER['argc'] == 2
&& in_array($_SERVER['argv'][1], array('--help', '-help', '-h', '-?'))
|| $_SERVER['argc'] < 2) {

    echo "Switch whitespace ready documentation to the new style\n\n";
    echo "Usage:      {$_SERVER['argv'][0]} <path_to_extension>\n";
    echo "            --help, -help, -h, -?      - to get this help\n";
    die;

}

// We only deal with the functions directory
$fullpath_dir = rtrim($_SERVER['argv'][1], '/') . '/functions/';

if (!is_dir($fullpath_dir)) {
    echo "ERROR: ($fullpath_dir) is not a directory.\n";
    exit(1);
}

$counts = array('old' => 0, 'new' => 0);
foreach (glob($fullpath_dir . "*.xml") as $file) {
    
    $old = file_get_contents($file);
    
    // Check if file is already using the new style and if so, skip it
    if (false !== strpos($old, '<refsect1 role=')) {
        $counts['new']++;
        continue;
    }
    
    // Switch description and examples
    $new = str_replace(
    array('<refsect1>', '<title>Description</title>', "  </para>\n  <para>\n   <example>"),
    array("\n".' <refsect1 role="description">', '&reftitle.description;', "  </para>\n </refsect1>\n\n <refsect1 role=\"examples\">\n  &reftitle.examples;\n  <para>\n   <example>"),
    $old);

    // Remove splitted from .. which doesn't make sense anymore
    $new = preg_replace('@<!-- splitted [^>]*>\s*\n@', '', $new);

    // Switch see also
    $new = preg_replace(
    '!  <para>
   See also:? <function>([^<]*)</function>\.?
  </para>
 </refsect1>!',
 ' </refsect1>

 <refsect1 role="seealso">
  &reftitle.seealso;
  <para>
   <simplelist>
    <member><function>\1</function></member>
   </simplelist>
  </para>
 </refsect1>',
 $new);
 
 $result = array(); 
 // Write parameters and return values
 preg_match('@<methodsynopsis>([(.\n]*)</methodsynopsis>@m', $old, $result);

 if (count($result)) {
     $params = array();
     preg_match_all('@<parameter[^>]*>([^<]*)</parameter>@', $result[1], $params);
     if (count($params) && count($params[1])) {
         $buffer = '</refsect1>

 <refsect1 role="parameters">
  &reftitle.parameters;
  <para>
   <variablelist>
';
         foreach ($params[1] as $param) {
             $buffer .="    <varlistentry>
     <term><parameter>$param</parameter></term>
     <listitem>
      <para>
      </para>
     </listitem>
    </varlistentry>
";
         }

         $buffer .= '   </variablelist>
  </para>
 </refsect1>

 <refsect1 role="returnvalues">
  &reftitle.returnvalues;
  <para>
  </para>
 </refsect1>';
     }
     $new = preg_replace('!</refsect1>!', $buffer, $new, 1);
 }
 
 if ($new === $old) {
     continue;
 }
 $counts['old']++;

 $fp = fopen($file, 'w');
 fputs($fp, $new);
 fclose($fp);

}

echo "Modified file information:\n";
echo "Old --> New: {$counts['old']}\n";
echo "Already New: {$counts['new']}\n";
