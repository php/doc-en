<?php
/*
  +----------------------------------------------------------------------+
  | ini doc settings updater                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2007 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Nuno Lopes <nlopess@php.net>                             |
  |             Jakub Vrána <vrana@php.net>                              |
  +----------------------------------------------------------------------+
*/


/* Configuration Options */

$php_src_dir = 'sources/php-src'; //php-src path
$pecl_dir    = 'sources/pecl';    //pecl path
$phpdoc_dir  = '../..';           //phpdoc path

define('MAX_MACRO_EXPAND_DEPTH', 10);

/******* END of configurations *****/

require_once './ini_search_lib.php';


/* Fix ini.xml files */

function fix_ini_xml($filename) {
    global $info;

    $original = $file = file_get_contents($filename);

    // insert the changelog column if it doesn't exist
    $file = preg_replace('@<tgroup\s+cols=[\'"]3[\'"]>(\s*<thead>\s*<row>\s*<entry>&?Name;?</entry>\s*<entry>&?Default;?</entry>(\s*)<entry>&?Changeable;?</entry>)(\s*</row>\s*</thead>)@US', '<tgroup cols="4">\1\2<entry>Changelog</entry>\3', $file);

    // remove old permissions constants usage about PHP_INI_PERDIR
    $file = preg_replace('/(?:PHP_INI_SYSTEM\s*\|\s*)?PHP_INI_PERDIR(?:\s*\|\s*PHP_INI_SYSTEM)?/', 'PHP_INI_PERDIR', $file);

    preg_match_all('@<tgroup\s+cols="4">.+<tbody>.+</tbody>.+</tgroup>@USs', $file, $matches);

    foreach ($matches[0] as $match) {
        preg_match_all('@<row>.+</row>@USs', $match, $matches_row);

        foreach ($matches_row[0] as $match_row) {
            preg_match_all('@<entry>.*</entry>@USs', $match_row, $matches_entry);

            foreach ($matches_entry as $val) {

		if (count($val) < 3) {
			echo "problem in $filename:\n" . print_r($val, 1) . "\n";
			continue;
		}

                // create changelog column
                if (count($val) == 3) {
                    $file = preg_replace("@(<row>\s*$val[0]\s*$val[1](\s*)".preg_quote($val[2]).')(\s*</row>)@', '\1\2<entry></entry>\3', $file);
                    $val[3] = '<entry></entry>';
                }

                // now update the info
                $entry = substr($val[0], 7, -8);
                if (isset($info[$entry])) {
                    $file = preg_replace("@(<row>\s*$val[0]\s*)$val[1](\s*)".preg_quote($val[2])."(\s*)$val[3](\s*</row>)@", "\\1<entry>{$info[$entry]['default']}</entry>\\2<entry>{$info[$entry]['permissions']}</entry>\\3<entry>{$info[$entry]['changelog']}</entry>\\4", $file);
                }
            }

        }

    }


    // if the file was modified, write the changes
    if ($original != $file) {
        file_put_contents($filename, $file);
        echo "Wrote $filename\n";
    }
}



/* Start the main program */

$array = array();
$replace = array();
recurse(array($pecl_dir, $php_src_dir), true);

$string = '';

echo 'Found ' . count($array) . " entries\n";

// get the changelog info
$included = true;
require_once './generate_changelog.php';
unset($info, $included, $error, $row);


/* &php.ini; only */
$special = array('disable_functions' => 1, 'disable_classes' => 1, 'expose_php' => 1, 'mail.force_extra_parameters' => 1);

/* Find links to documentation */
$links       = array();
$link_files  = array();
$ini_files   = glob("$phpdoc_dir/en/reference/*/ini.xml");
$ini_files[] = "$phpdoc_dir/en/features/safe-mode.xml";
$ini_files[] = "$phpdoc_dir/en/appendices/ini.xml";

foreach ($ini_files as $filename) {

    preg_match_all('~<varlistentry\s+(?:xml:)?id="(ini\.[^"]+)">(.*)</varlistentry>~USs', file_get_contents($filename), $matches, PREG_SET_ORDER);
    foreach ($matches as $varlistentry) {
        preg_match_all('~<term>.*<parameter>(.*)</parameter>~USs', $varlistentry[2], $matches2);
        foreach ($matches2[1] as $parameter) {
            $links[trim($parameter)] = $varlistentry[1];
            $link_files[trim($parameter)] = $filename;
        }
    }
}


if (!$idx = sqlite_open('ini_changelog.sqlite', 0666, $error)) {
    die("Couldn't open the DB: $error");
}

// fetch info from removed opts
foreach ($changelog as $name => $entry) {
    if (empty($array[$name])) {
        $q = sqlite_unbuffered_query($idx, "SELECT defaultval, permissions FROM last_seen_values WHERE name='$name'");
        $array[$name] = sqlite_fetch_array($q, SQLITE_NUM);
    }
}

sqlite_close($idx);
unset($idx);


// remove bogus ini opt values
$bogus = array('pdo.global_value');
foreach ($bogus as $x) {
    unset($array[$x]);
}

uksort($array, 'strnatcasecmp');


/* Generate the XML code */
foreach($array as $entry => $arr) {

    $oldentry = $entry;

    /* link entries */
    if (isset($links[$entry])) {
        $entry = '<link linkend="' . $links[$entry] . '">' . $entry . '</link>';
        unset($link_files[$oldentry]);
    }


    /* replace macros and make the $default var */
    $new = $arr[0];
    $old = null;
    $i = 0;

    while ($new[0] !== '"' && $new !== $old && ++$i < MAX_MACRO_EXPAND_DEPTH) {
        $old = $new;
        $new = strtr($new,$replace);
    }

    $default = $new;

    if(preg_match_all('/"([^"]+)"/S', $default, $match) > 1) {
        $default = '"';

        foreach($match[1] as $add) {
            $default .= $add;
        }
        $default .= '"';
    }

    // replace the @PREFIX@ stuff
    $default = preg_replace(array('~@PREFIX@~i', '~[\\\\]{2}~'), array('/path/to/php', '/'), $default);

    /* end of $default stuff */

    $permissions = isset($special[$oldentry]) ? '&php.ini; only' : $arr[1];


    $info[$oldentry]['default']     = $default;
    $info[$oldentry]['permissions'] = $permissions;
    $info[$oldentry]['changelog']   = isset($changelog[$oldentry]) ? $changelog[$oldentry] : '';


    $string .= '      <row>' . PHP_EOL.
               "       <entry>$entry</entry>" . PHP_EOL.
               "       <entry>$default</entry>" . PHP_EOL.
               "       <entry>$permissions</entry>" . PHP_EOL.
               "       <entry>{$info[$oldentry]['changelog']}</entry>" . PHP_EOL.
               '      </row>'.PHP_EOL;
}


/* Print unmatched links */
$deprecated = array('debugger.host', 'debugger.port', 'debugger.enabled', 'sesam_oml', 'sesam_configfile', 'sesam_messagecatalog', 'allow_webdav_methods', 'warn_plus_overloading');
foreach ($deprecated as $val) {
    unset($link_files[$val]);
}
if ($link_files) {
    echo "Warning - unmatched links:\n";
    foreach ($link_files as $ini => $file) {
        echo str_pad($ini, 30, ' ', STR_PAD_RIGHT) . ' => ' . substr($file, strlen($phpdoc_dir)+4) . "\n";
    }
}


/* Now write the final result */
$file = file_get_contents("$phpdoc_dir/en/appendices/ini.xml");

$pos = strpos($file, '<tbody>', strpos($file, '<title>Configuration options</title>')) + strlen('<tbody>');
$pos2 = strpos($file, '</tbody>', $pos);

file_put_contents("$phpdoc_dir/en/appendices/ini.xml", substr($file, 0, $pos) . PHP_EOL . $string . '     ' . substr($file, $pos2));
echo "\n\nWrote the main table\n";


/* fix ini.xml files (if needed) */
foreach ($ini_files as $file) {
    fix_ini_xml($file);
}

?>
