<?php
/*
 * Translation to PHP of the old script mk_ini_set.sh to
 * generate a list of PHP config options and where they
 * can be set.
 * Author: Jesus M. Castagnetto
 * $Id$
 */

$phpsrc_dir = '';
// use command line parameter is available
if ($argc == 2 && $argv[1] != '') {
    $phpsrc_dir = $argv[1];
}
//$phpsrc_dir = '/cvs/php5';
// figure out the php4 source dir
if ($phpsrc_dir == '') {
    if (file_exists('../php4')) {
        $phpsrc_dir = realpath('../php4');
    } else if (file_exists('../../php4')) {
        $phpsrc_dir = realpath('../../php4');
    } else {
        die ("Cannot find PHP4 dir, set phpsrc_dir to the full path\n");
    }
}

// figure out the phpdoc dir
$phpdoc_dir = '';
if ($phpdoc_dir == '') {
    $current = getcwd();
    if (preg_match('/\/phpdoc$/', $current)) {
        $phpdoc_dir = $current;
    } else {
        $tmp = str_replace(strrchr($current,'/'),'',$current);
        if (preg_match('/\/phpdoc$/', $tmp)) {
            $phpdoc_dir = $tmp;
        } else {
            die ("Cannot find PHPDOC dir, set phpdoc_dir to the full path\n");
        }
    }
}

$master_ini_table = '';

echo "Using:\nPHP4 SRC DIR: $phpsrc_dir\nPHPDOC DIR: $phpdoc_dir\n\n";

$inixml_header = <<<INIXML_HEADER
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- Automatically generated using gen_PHP_INI_ENTRY.php -->
<!-- DO NOT EDIT. -->
<!-- \$Revision$ -->
<section id="##ID##.configuration">
 &reftitle.runtime;
 &extension.runtime;\n\n
INIXML_HEADER;

$inixml_footer = <<<INIXML_FOOTER
</section>
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
sgml-default-dtd-file:"../../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=sgml
vi: ts=1 sw=1
-->
INIXML_FOOTER;
 
$legend = <<<LEGEND
 <para>
  Read the manual section on <link linkend="configuration">
  Configurations</link> for more information in regards to setting 
  PHP configurations.  The <literal>PHP_INI_*</literal> 
  <link linkend="language.constants">constants</link> used in the 
  table below are defined as follows:
 </para>
 <para>
  <table>
   <thead>
    <row>
     <entry>Constant</entry>
     <entry>Value</entry>
     <entry>Meaning</entry>
    </row>
   </thead>
   <tbody>
    <row>
     <entry><constant>PHP_INI_USER</constant></entry>
     <entry>1</entry>
     <entry>Entry can be set in user scripts</entry>
    </row>
    <row>
     <entry><constant>PHP_INI_PERDIR</constant></entry>
     <entry>2</entry>
     <entry>Entry can be set in <filename>.htaccess</filename></entry>
    </row>
    <row>
     <entry><constant>PHP_INI_SYSTEM</constant></entry>
     <entry>4</entry>
     <entry>Entry can be set in <filename>php.ini</filename> or
      <filename>httpd.conf</filename></entry>
    </row>
    <row>
     <entry><constant>PHP_INI_ALL</constant></entry>
     <entry>7</entry>
     <entry>Entry can be set anywhere</entry>
    </row>
   </tbody>
  </table>
 </para>\n
LEGEND;

$table_header = <<<TABLE_HEADER
 <para>
  <table>
   <title>Configuration options</title>
   <tgroup cols="3">
   <thead>
    <row>
     <entry>Name</entry>
     <entry>Default</entry>
     <entry>Changeable</entry>
    </row>
   </thead>
   <tbody>\n
TABLE_HEADER;

$table_footer = <<<TABLE_FOOTER
   </tbody>
  </tgroup>
 </table>
</para>\n
TABLE_FOOTER;


function gentree($path, $remove_empty = false, $fileproc_cb = null) {/*{{{*/
    $excludeitems = array ('CVS', 'tests', 'skeleton.c');
    if (!file_exists($path)) {
        die("BAD PATH $path\n");
    }
    $tree = array();
    chdir($path);
    $all = glob('*');
    foreach ($all as $item) {
        $fullpath = "{$path}/{$item}";
        if (in_array($item, $excludeitems)) {
            continue;
        } else if (is_dir($fullpath)) {
            $subtree = gentree($fullpath, $remove_empty, $fileproc_cb);
            if ($remove_empty && !empty($subtree)) {
                $tree[$fullpath] = $subtree;
            } else {
                continue;
            }
        } else if (preg_match('/\.[ch]$/', $item)) {
            if (is_null($fileproc_cb)) {
                $tree[$item] = $fullpath;
            } else {
                $res = $fileproc_cb($fullpath);
                if (!is_null($res)) {
                    $tree[$item] = $res;
                } else {
                    continue;
                }
            }
        }
    }
    return $tree;
}/*}}}*/

function findINI($fname) {/*{{{*/
    $found = array();
    if (!is_readable($fname)) {
        return "CANNOT READ FILE: $fname";
    }
    $data = file_get_contents($fname);
    //$re = '/PHP_INI_ENTRY\("([^"]+)",\s+"([^"]+)",\s+([A-Z_]),/';
    $re = '/(PHP_INI_ENTRY|PHP_INI_ENTRY_EX|PHP_INI_BOOLEAN)\(([^)]+)/';
    preg_match_all($re, $data, &$matches);
    $re2 = '/"([^"]+)",\s*"([^"]+)",\s*([A-Z_]+)/';

    foreach ($matches[2] as $match) {
        if(preg_match($re2, $match, $entry)) {
        //$match = str_replace('"','',$match);
        //$entry = preg_split('/,\s*/', $match);
        // dummy settings seem to always have these values (ex. ncurses.c)
            if ($entry[1] == 42 || $entry[1] == 'foobar') {
                continue;
            }
            $found['INI'][$entry[1]] = array(
                                'def' => $entry[2], 
                                'mod' => str_replace(array(' ', "\n","\r","\t"),'',$entry[3])
                                );
            }
    }
    if (!empty($found)) {
        return $found;
    } else {
        return null;
    }
}/*}}}*/

function flatentree($tree, $section) {
    static $flat = array();
    foreach ($tree as $node=>$val) {
        if (array_key_exists($section, $val)) {
            $flat[$node] = $val[$section];
        } else {
            flatentree($val, $section);
        }
    }
    return $flat;
}

$dtree = gentree($phpsrc_dir, true, 'findINI');
print_r($dtree);
/*
$ser = serialize($dtree);

$fp = fopen('/tmp/PHPINIDEFS.ser', 'w');
fwrite($fp, $ser);
fflush($fp);
fclose($fp);
*/

function createINI($dir, $cfgs) {
    global $master_ini_table;
    
    $rows = '';
    foreach ($cfgs as $name=>$vals) {
        $rows .= "     <row>\n";
        $rows .= "      <entry>$name</entry>\n";
        if ($vals['def'] == 'NULL') {
            $default = "''";
        } elseif (preg_match('/^[A-Z_]+$/',$vals['def'])) {
            if (defined($vals['def'])) {
                $default = "'".htmlspecialchars(constant($vals['def']))."'";
            } else {
                $default = $vals['def'];
            }
        } else {
            if (is_numeric($vals['def'])) {
                if (intval($vals['def']) == 1) {
                    $default = 'On';
                } elseif (intval($vals['def']) == 0) {
                    $default = 'Off';
                } else {
                    $default = $vals['def'];
                }
            } else {
                $default = "'".htmlspecialchars($vals['def'])."'";
            }
        }
        $rows .= "      <entry>{$default}</entry>\n";
        $rows .= "      <entry><constant>{$vals['mod']}</constant></entry>\n";
        $rows .= "     </row>\n";
    }
    $master_ini_table .= $rows;
    
    if ($dir == 'en/chapters') {
        $id = 'general';
    } else {
        $id = basename($dir);
    }
    $out = str_replace('##ID##',$id, $GLOBALS['inixml_header']);
    $out .= $GLOBALS['legend'];
    $out .= $GLOBALS['table_header'].$rows.$GLOBALS['table_footer'];
    $out .= $GLOBALS['inixml_footer'];
    $fp = fopen("{$GLOBALS['phpdoc_dir']}/{$dir}/test_ini.xml", 'w');
    if (is_resource($fp)) {
        fwrite($fp, $out);
        fflush($fp);
        fclose($fp);
        echo "CREATED {$GLOBALS['phpdoc_dir']}/{$dir}/test_ini.xml\n";
    } else {
        echo "ERROR CREATING {$GLOBALS['phpdoc_dir']}/{$dir}/test_ini.xml\n";
    }
}

function createMasterINI ($dir, $rows) {
    $fp = fopen("{$GLOBALS['phpdoc_dir']}/{$dir}/config.master_test.xml", 'w');
    $out = str_replace('##ID##','master', $GLOBALS['inixml_header']);
    $out .= $GLOBALS['legend'];
    $out .= $GLOBALS['table_header'].$rows.$GLOBALS['table_footer'];
    $out .= $GLOBALS['inixml_footer'];
    if (is_resource($fp)) {
	fwrite($fp, $out);
	fflush($fp);
	fclose($fp);
	echo "CREATED {$GLOBALS['phpdoc_dir']}/{$dir}/config.master_test.xml\n";
    }	
}	

// flatten tree
$flat = flatentree($dtree, 'INI');
print_r($flat);

// map doc dirs w/ the appropriate set of source files
$map = array (/*{{{*/
    'en/chapters' => 'main.c,basic_functions.c',
	'en/reference/apache' => 'php_apache.c',
	'en/reference/array' => '',
	'en/reference/aspell' => '',
	'en/reference/bc' => '',
	'en/reference/bzip2' => '',
	'en/reference/calendar' => '',
	'en/reference/ccvs' => '',
	'en/reference/classobj' => '',
	'en/reference/com' => 'com.c',
	'en/reference/cpdf' => '',
	'en/reference/crack' => 'crack.c',
	'en/reference/ctype' => '',
	'en/reference/curl' => '',
	'en/reference/cybercash' => '',
	'en/reference/cyrus' => '',
	'en/reference/datetime' => '',
	'en/reference/dba' => 'dba.c',
	'en/reference/dbase' => '',
	'en/reference/dbm' => '',
	'en/reference/dbplus' => '',
	'en/reference/dbx' => '',
	'en/reference/dio' => '',
	'en/reference/dir' => '',
	'en/reference/domxml' => '',
	'en/reference/dotnet' => '',
	'en/reference/errorfunc' => '',
	'en/reference/exec' => '',
	'en/reference/fbsql' => '',
	'en/reference/fdf' => '',
	'en/reference/filepro' => '',
	'en/reference/filesystem' => 'file.c',
	'en/reference/fribidi' => '',
	'en/reference/ftp' => '',
	'en/reference/funchand' => '',
	'en/reference/gettext' => '',
	'en/reference/gmp' => '',
	'en/reference/http' => '',
	'en/reference/hw' => 'hw.c',
	'en/reference/hwapi' => '',
	'en/reference/ibase' => 'interbase.c',
	'en/reference/icap' => '',
	'en/reference/iconv' => 'iconv.c',
	'en/reference/ifx' => '',
	'en/reference/iisfunc' => '',
	'en/reference/image' => 'exif.c',
	'en/reference/imap' => '',
	'en/reference/info' => 'assert.c',
	'en/reference/ingres-ii' => 'ii.c',
	'en/reference/ircg' => 'ircg.c',
	'en/reference/java' => '',
	'en/reference/ldap' => 'ldap.c',
	'en/reference/mail' => '',
	'en/reference/mailparse' => '',
	'en/reference/math' => '',
	'en/reference/mbstring' => 'mbstring.c',
	'en/reference/mcal' => '',
	'en/reference/mcrypt' => 'mcrypt.c',
	'en/reference/mcve' => '',
	'en/reference/mhash' => '',
	'en/reference/mime_magic' => 'mime_magic.c',
	'en/reference/ming' => '',
	'en/reference/misc' => '',
	'en/reference/mnogosearch' => '',
	'en/reference/msession' => '',
	'en/reference/msql' => '',
	'en/reference/mssql' => 'php_mssql.c',
	'en/reference/muscat' => '',
	'en/reference/mysql' => 'php_mysql.c',
	'en/reference/ncurses' => 'ncurses.c',
	'en/reference/network' => '',
	'en/reference/nis' => '',
	'en/reference/notes' => '',
	'en/reference/objaggregation' => '',
	'en/reference/oci8' => '',
	'en/reference/openssl' => '',
	'en/reference/oracle' => '',
	'en/reference/outcontrol' => '',
	'en/reference/overload' => '',
	'en/reference/ovrimos' => '',
	'en/reference/pcntl' => '',
	'en/reference/pcre' => '',
	'en/reference/pdf' => '',
	'en/reference/pfpro' => 'pfpro.c',
	'en/reference/pgsql' => 'pgsql.c',
	'en/reference/posix' => '',
	'en/reference/printer' => '',
	'en/reference/pspell' => '',
	'en/reference/qtdom' => '',
	'en/reference/readline' => '',
	'en/reference/recode' => '',
	'en/reference/regex' => '',
	'en/reference/sem' => '',
	'en/reference/sesam' => '',
	'en/reference/session' => 'session.c,url_scanner_ex.c',
	'en/reference/shmop' => '',
	'en/reference/snmp' => '',
	'en/reference/sockets' => '',
	'en/reference/stream' => '',
	'en/reference/strings' => '',
	'en/reference/swf' => '',
	'en/reference/sybase' => 'php_sybase_ct.c',
	'en/reference/tokenizer' => 'tokenizer.c',
	'en/reference/uodbc' => 'php_odbc.c',
	'en/reference/url' => '',
	'en/reference/var' => '',
	'en/reference/vpopmail' => '',
	'en/reference/w32api' => '',
	'en/reference/wddx' => '',
	'en/reference/xml' => '',
	'en/reference/xmlrpc' => '',
	'en/reference/xslt' => '',
	'en/reference/yaz' => 'php_yaz.c',
	'en/reference/zip' => '',
	'en/reference/zlib' => 'zlib.c',
	);/*}}}*/
// now walk through the map and generate the appropriate ini.xml files
foreach ($map as $dir=>$srcfiles) {
    if ($srcfiles == '') {
        continue;
    } else {
        $files = explode(',', $srcfiles);
        $cfgs = array();
        foreach ($files as $fname) {
            $cfgs = array_merge($cfgs, $flat[$fname]);
        }
        ksort($cfgs);
        createINI($dir, $cfgs);
    }
}

createMasterINI('en/chapters', $master_ini_table);

?>
