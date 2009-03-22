<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2009 The PHP Group                                |
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

/**
 * @todo 
 * Add support for online doc editing
 * Add count of "perfect" functions
 * Get this on http://doc.php.net/
 */

$errors = array(
'undoc' => array(
'label' => 'Not documented',
'description' => 'These are the undocumented functions: The function which XML skeleton is in CVS, and contain &warn.undocumented.func;'
),
'oldstyle' => array(
'label' => 'Old style',
'description' => 'These functions are not converted to the new doc style yet, and thus, are not checked',
),
'badorder' => array(
'label' => 'Bad refsect1 order',
'description' => 'These functions are converted to the new doc style, but the refsect1 are not well ordered',
),
'noparameters' => array(
'label' => 'No parameters',
'description' => 'These functions lacks parameters description.',
),
'noreturnvalues' => array(
'label' => 'No return values',
'description' => 'These functions lacks return values information',
),
'noexamples' => array(
'label' => 'No examples',
'description' => 'These functions lacks examples.',
),
'nochangelog' => array(
'label' => 'No changelog section',
'description' => 'These functions lacks changelog refsect1 while metionning "PHP \d" in the XML source.',
),
'noerrors' => array(
'label' => 'No errors section',
'description' => 'These functions lacks errors information.',
),
'noseealso' => array(
'label' => 'No see also',
'description' => 'These functions lacks link to other functions/sections. You may consider adding some cross links to point readers to valuable resources.',
),

'roleerror' => array(
'label' => 'Refsect1 role error',
'description' => 'One or more &lt;refsect1&gt; tags use an unknown role attribute value',
),


);

$restrict = (isset($_GET['restrict']) && isset($errors[$_GET['restrict']])) ? $_GET['restrict'] : false;

echo '<?xml version="1.0"?>' . "\n";
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>PHPDOC Check<?php
  if ($restrict && isset($errors[$restrict])) {
      echo ' > ' . $errors[$restrict]['label'];
  }
    ?></title>
  <style>
   * { font-family:verdana,arial,helvetica,sans-serif; font-size:98%;}
   h1, h2 { font-size:130%; color:#000066; font-weight:bold;}
   tr.h:hover { background-color: #FAEBD7;}
   dt {color: #000066;font-weight: bold;}
   dd {font-style: italic;}
   table.stats {border-collapse: collapse; border: 1px solid black; width: 100%;}
   table.stats td { border: 1px solid gray;}
   tr.subheader {background-color: #9999cc;color:#fff;font-size: 95%;}
   tr.subheader a {color: #fff;}
   td.err {background-color: #DEDEDE;}
   h2 { margin: 0; margin-top: 10px;}
  </style>
 </head>
 <body>
<?php

$dbhandle = sqlite_open('check_phpdoc.sqlite');

echo '<h1>PHPDOC Check</h1>';

if (!$restrict) {

    $order = isset($_GET['order']) && $_GET['order'] == 'DESC' ? 'DESC' : 'ASC';
    $sort  = isset($_GET['sort']) && in_array($_GET['sort'], array_keys($errors)) ? 'SUM(' . $_GET['sort'] . ')' : 'extension';

    $sql = 'SELECT extension, COUNT(*) AS total, SUM(' . implode('), SUM(', array_keys($errors)) . ') FROM reference GROUP BY extension ORDER BY ' . $sort . ' ' . $order;
    $result = sqlite_query($dbhandle, $sql);
    $extensions = array();
    while ($row = sqlite_fetch_array($result, SQLITE_ASSOC)) {
        $extensions[$row['extension']] = $row;
    }

    echo '<p>
This script parses the <i>reference/</i> directory of the PHPDOC module and checks for common problems in the documentation. For now, supported tests are:
  
 <ul>';
    foreach ($errors as $type => $info) {
        echo "<li>{$errors[$type]['label']} (<a href=\"{$_SERVER['PHP_SELF']}?restrict=$type\">Restrict</a>)</li>";
    }
    echo ' </ul>
</p>';

    echo '<table class="stats">';
    echo '<tr class="subheader">';
    echo '<td>Extension</td>';
    $order = ($order == 'ASC') ? 'DESC' : 'ASC';
    foreach ($errors as $type => $info) {
        echo "<td><a href=\"view_check_phpdoc.php?sort=$type&order=$order\">{$info['label']}</a></td>";
    }
    echo '</tr>';
    foreach ($extensions as $extension => $stats) {
        echo "<tr class=\"h\">";
        echo "<td><a href=\"http://php.net/$extension\">$extension</a></td>";
        foreach ($errors as $type => $info) {
            echo "<td title=\"{$info['label']}\"". ($stats['SUM(' . $type . ')'] ? ' class="err"><a href="view_check_phpdoc.php?restrict=' . $type . '#' . $extension . '">' . $stats['SUM(' . $type . ')'] . '</a>' : '> '). "</td>";
        }
        echo "</tr>";
    }
    echo '</table>';

} else {

    $query  = sqlite_query($dbhandle, 'SELECT * FROM reference WHERE ' . $restrict . ' = 1');
    $status = array();
    $result = sqlite_fetch_all($query, SQLITE_ASSOC);
    foreach ($result as $res) {
        $status[$res['extension']][$res['funcname']] = $res;
    }

    echo '<p>
 <dl>';
    echo "<dt>{$errors[$restrict]['label']} (<a href=\"{$_SERVER['PHP_SELF']}\">All</a>)</dt><dd>{$errors[$restrict]['description']}</dd>";
    echo ' </dl>
</p>';
    echo '<p>';
    foreach ($status as $extension => $functions) {
        $nb = count($functions);
        if ($nb != 0) {
            $funcn += $nb;
            $exts .= '<a href="#' . $extension . '">' . $extension . '</a> ';
        }
    }
    echo "$funcn functions from " . count($status) . " extensions: $exts";
    echo '
      </p>';

    echo '<table width="100%">';
    foreach ($status as $extension => $functions) {
        $i = 0;
        echo '<tr class="subheader" id="' . $extension . '"><td colspan="4" align="center">' . $extension . ' (' . count($functions) . ')</td></tr><tr>';

        foreach ($functions as $function => $problems) {
            if (!isset($problems[$restrict])) {
                continue;
            }
            
            if ($i % 4 == 0) {
                echo '<tr>';
            }
            $i++;
            echo "<td><a href=\"http://php.net/" . substr($function, 0, -4) . "\">$function</a></td>";
            if ($i % 4 == 0) {
                echo '</tr>';
            }
        }


    }
    echo '</table>';
}
?></body>
</html>
