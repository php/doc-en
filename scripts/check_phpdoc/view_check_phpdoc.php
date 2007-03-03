<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
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
   tr.header {background-color:#000066;color:#fff;}
   tr.subheader {background-color:#E0E0E0;color:#000066;font-size: 95%;}
   td.err {background-color: #f00;}
   h2 { margin: 0; margin-top: 10px;}
  </style>
 </head>
 <body>
<?php

$dbhandle = sqlite_open('check_phpdoc.sqlite');
$where = '';

if ($restrict) {
    $where = ' WHERE ' . $restrict . ' = 1';
}

$query  = sqlite_query($dbhandle, 'SELECT * FROM reference' . $where);
$status = array();
$result = sqlite_fetch_all($query, SQLITE_ASSOC);
foreach ($result as $res) {
    $status[$res['extension']][$res['funcname']] = $res;
}

echo '<h1>PHPDOC Check</h1>';

if (!$restrict) {
    echo '<p>
This script parses the <i>reference/</i> directory of the PHPDOC module and checks for common problems in the documentation. For now, supported tests are:
  
 <ul>';
    foreach ($errors as $type => $info) {
        echo "<li>{$errors[$type]['label']} (<a href=\"{$_SERVER['PHP_SELF']}?restrict=$type\">Restrict</a>)</li>";
    }
    echo ' </ul>
</p>';

    $exts = '';
    $funcn = 0;
    foreach ($status as $extension => $functions) {
        $nb = count($functions);
        if ($nb != 0) {
            $funcn += $nb;
            $exts .= '<a href="#' . $extension . '">' . $extension . '</a> ';
        }
    }
    echo "The following $funcn functions from " . count($status) . " extensions lacks some information: $exts";
    echo '</p>';
    echo '<table width="100%">';
    $cols = count($errors) + 1;
    foreach ($status as $extension => $functions) {
        // Skip if no functions
        if (count($functions) == 0) {
            continue;
        }
        echo '<tr class="header" id="' . $extension . '"><td colspan="' . $cols . '" align="center">' . $extension . ' (' . count($functions) . ')</td></tr>';
        echo '<tr class="subheader">
             <td></td>
             <td>Not documented</td>
             <td>Old Style</td>
             <td>Bad refsect1 order</td>
             <td>No parameters</td>
             <td>No return values</td>
             <td>No examples</td>
             <td>No errors</td>
             <td>No see also</td>
             <td>Role error</td>
          </tr>';
        foreach ($functions as $function => $problems) {
            echo "<tr class=\"h\">
                  <td><a href=\"http://php.net/" . substr($function, 0, -4) . "\">$function</a></td>";
            echo "<td" . (isset($problems['undoc']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['oldstyle']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['badorder']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['noparameters']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['noreturnvalues']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['noexamples']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['noerrors']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['noseealso']) ? ' class="err">' : '>') . "</td>";
            echo "<td" . (isset($problems['roleerror']) ? ' class="err">' : '>') . "</td>";
            echo "</tr>";
        }
    }
    echo '</table>';
} else {
    $type = $restrict;
    echo '<p>
 <dl>';
    echo "<dt>{$errors[$type]['label']} (<a href=\"{$_SERVER['PHP_SELF']}\">All</a>)</dt><dd>{$errors[$type]['description']}</dd>";
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
        echo '<tr class="header" id="' . $extension . '"><td align="center">' . $extension . ' (' . count($functions) . ')</td></tr>';
        foreach ($functions as $function => $problems) {
            if (!isset($problems[$type])) {
                continue;
            }
            echo "<tr>
                  <td><a href=\"http://php.net/" . substr($function, 0, -4) . "\">$function</a></td>";
            echo "</tr>";
        }
    }
    echo '</table>';
}
?></body>
</html>