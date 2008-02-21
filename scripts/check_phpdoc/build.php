<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2008 The PHP Group                                |
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
 * Improve support for deprecated functions (introduce a new entity in phpdoc?)
 * Check for role="reference" on parameters
 * Check against php-src for parameters/return values (parse the C code, not protos)
 * Add various tests for parameters
 */

error_reporting(E_ALL);

$status = array();

foreach (glob('../../en/reference/*/*/*.xml') as $function) {

    $path = explode('/', $function);
    $extension = $path[count($path) - 3];

    $funcname = basename($function);

    // Skip main
    if ($funcname == 'main.xml' || strpos($funcname, 'entities.') === 0) {
        continue;
    }

    $xmlstr = str_replace('&', '&amp;', file_get_contents($function));

    $xml = new DOMDocument();
    $xml->preserveWhiteSpace = false;


    if (!@$xml->loadXml($xmlstr)) {
        echo "XML Parse Error: $function\n";
        continue;
    }

    // Variables initialisation
    $noparameters = false;
    $returnvoid = false;

    $refsect1s  = $xml->getElementsByTagName('refsect1');
    foreach ($refsect1s as $refsect1) {
        $role = $refsect1->getAttribute('role');
        switch ($role) {
            case 'description':

                // Get text buffer for various checks
                $whole_description = $refsect1->nodeValue;

                // If not documented, mark it and skip to next function
                if (strpos($whole_description, '&warn.undocumented.func;') !== false) {
                    $status[$extension][$funcname]['undoc'] = 1;
                    continue 3;
                }

                // If deprecated, skip to next function
                // @todo: add a better way of handling this (new entity in phpdoc?)
                if (strpos($whole_description, 'This function is deprecated') !== false) {
                    continue 3;
                }

                // If an alias, skip to next function
                if (strpos($whole_description, '&info.function.alias;') !== false) {
                    continue 3;
                } else {
                    $refnamedivs  = $xml->getElementsByTagName('refnamediv');
                    foreach ($refnamedivs as $refnamediv) {
                        if (stripos($refnamediv->nodeValue, 'alias') !== false) {
                            continue 4;
                        }
                    }
                }

                // Look into the methodsynopsys tag(s)
                $methodsynopsiss =  $xml->getElementsByTagName('methodsynopsis');
                foreach ($methodsynopsiss as $methodsynopsis) {
                    foreach ($methodsynopsis->childNodes as $child) {
                        switch ($child->nodeName) {
                            case '#comment':
                                // Skip comments
                                continue;

                            case 'type':
                                // This is the return type
                                break;

                            case 'void':
                                // This either the return type or 0 parameters
                                if (!isset($methodname)) {
                                    $returnvoid = true;
                                } else { // no parameters
                                    $noparameters = true;
                                }
                                break;

                            case 'methodname':
                                $methodname = $child->nodeValue;
                                break;

                            case 'methodparam':
                            case 'modifier':
                                break;

                            default:
                                echo "Unknown child for methodsynopsis: {$child->nodeName} in $function\n";
                        }
                    }
                }


                break;

            case 'returnvalues':
            case 'parameters':
            case 'seealso':
            case 'examples':
            case 'notes':
            case 'changelog':
            case 'errors':
                // test order
                switch ($role) {
                    case 'parameters':
                        if (isset($notes) && isset($changelog) && isset($returnvalues) && isset($examples) && isset($seealso)) {
                            $status[$extension][$funcname]['badorder'] = 1;
                        }
                        break;
                    case 'returnvalues':
                        if (isset($notes) && isset($changelog) && isset($examples) && isset($seealso)) {
                            $status[$extension][$funcname]['badorder'] = 1;
                        }
                        break;
                    case 'changelog':
                        if (isset($notes) && isset($examples) && isset($seealso)) {
                            $status[$extension][$funcname]['badorder'] = 1;
                        }
                        break;
                    case 'examples':
                        if (isset($notes) && isset($seealso)) {
                            $status[$extension][$funcname]['badorder'] = 1;
                        }
                        break;
                    case 'notes':
                        if (isset($seealso)) {
                            $status[$extension][$funcname]['badorder'] = 1;
                        }
                        break;
                }
                $$role = 1;
                $whole_content = $refsect1->nodeValue;

                // Check for default stub generated by xml_proto
                if ($role == 'returnvalues' && strpos($whole_content, 'What the function returns, first on success, then on failure.') !== false) {
                    unset($returnvalues);
                }
                break;

            default:
                if ($role != '') {
                    $status[$extension][$funcname]['roleerror'] = 1;
                } else {
                    $status[$extension][$funcname]['oldstyle'] = 1;
                    // Skip the remaining refsect1
                    continue 3;
                }
        }
    }

    // See also checks
    if (!isset($seealso)) {
        $status[$extension][$funcname]['noseealso'] = 1;
    }
    unset($seealso);

    // Return Values
    if (!isset($returnvalues)) {
        $status[$extension][$funcname]['noreturnvalues'] = 1;
    }
    unset($returnvalues);

    // Parameters
    if (!isset($parameters) && !$noparameters) {
        $status[$extension][$funcname]['noparameters'] = 1;
    }
    unset($parameters);

    // Examples checks
    if (!isset($examples)) {
        $status[$extension][$funcname]['noexamples'] = 1;
    }
    unset($examples);

    // Errors checks
    if (!isset($errors)) {
        $status[$extension][$funcname]['noerrors'] = 1;
    }
    unset($errors);
}

$idx = sqlite_open("check_phpdoc.sqlite");

sqlite_query($idx, 'CREATE TABLE reference (
extension char(40),
funcname char(200),
oldstyle integer,
undoc integer,
roleerror integer,
badorder integer,
noseealso integer,
noreturnvalues integer,
noparameters integer,
noexamples integer,
noerrors integer
);');


$qry_str = '';
foreach ($status as $extension => $functions) {
    foreach ($functions as $function => $attrs) {
        $qry_str .= 'INSERT INTO reference (extension, funcname, ' . implode(', ', array_keys($attrs)) . ') VALUES ("' . $extension . '", "' . $function . '", ' . implode(', ', $attrs) . ');';
    }
}
sqlite_query($idx, $qry_str);
unset($qry_str);
sqlite_close($idx);

