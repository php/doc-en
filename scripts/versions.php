#!/usr/bin/php
<?php
/*
  +----------------------------------------------------------------------+
  | PHP Version 4                                                        |
  +----------------------------------------------------------------------+
  | Copyright (c) 1997-2011 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.0 of the PHP license,       |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_0.txt.                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors:    Jakub Vrana <vrana@php.net>                              |
  +----------------------------------------------------------------------+

Print functions with missing and wrong version information.
This parses PHP source codes as compiling them or downloading binaries is slow.
The parser isn't perfect so there are some false positives and negatives.
Requires ../version.xml created by ../configure.php.
*/

$php_src = "../../../php-src";

chdir(__DIR__ . "/$php_src");
$tags = array();
exec("git tag", $output);
foreach ($output as $tag) {
	if (preg_match('~^php-([0-9.]+)$~', $tag, $match)) {
		$tags[$tag] = $match[1];
	}
}
uksort($tags, 'version_compare');

$cache = __DIR__ . "/versions.ser";
if (file_exists($cache)) {
	$versions = unserialize(file_get_contents($cache));
} else {
	$versions = array(); // function => array(major => array(min, max))
	foreach ($tags as $tag => $version) {
		if ($version < 5) {
			continue;
		}
		echo "$tag: ";
		passthru("git checkout -q $tag", $return);
		if ($return) {
			continue;
		}
		$major = substr($version, 0, 1);
		echo versions($versions, $major, $version) . " functions\n";
	}
	passthru("git checkout master");
	file_put_contents($cache, serialize($versions));
	echo "\n";
}

function versions(&$versions, $major, $version) {
	$aliases = array();
	$classes = array();
	$return = 0;
	foreach (rglob("*/") as $dirname) {
		$macros = array();
		$files = array();
		foreach (glob("$dirname*.[ch]*") as $filename) {
			$files[$filename] = $file = file_get_contents($filename);
			preg_match_all("~^#define[ \t]+(\\w+)(\\([^)]+\\))?([ \t]+.+[^\\\\])\$~msU", $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $val) {
				$params = preg_split('~,\\s*~', trim($val[2], '()'));
				$macros[$val[1]] = array(trim(str_replace(array("\r", "\\\n"), "", $val[3])), $params);
			}
		}

		foreach ($files as $filename => $file) {
			// expand macros
			if ($macros && strlen(implode('|', array_keys($macros))) < 32000) {
				$files[$filename] = $file = preg_replace_callback('~\\b(' . implode('|', array_keys($macros)) . ')\\b(\\(.*\\))?~', function ($matches) use ($macros) {
					$macro = $macros[$matches[1]];
					if ($matches[2]) {
						$params = explode(",", trim($matches[2], '()'), count($macro[1]));
						return str_replace($macro[1], $params, $macro[0]);
					}
					return $macro[0];
				}, $file);
			}
			// named functions
			preg_match_all('~(?:PHP|ZEND)_NAMED_FE\\((\\w+)\\s*,\\s*(\\w+)~', $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$aliases[$match[2]] = $match[1];
			}
			// methods
			preg_match_all('~INIT(?:_OVERLOADED)?_CLASS_ENTRY\\(.*"([^"]+)"\\s*,\\s*([^)]+)~', $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				if (preg_match('~' . preg_quote($match[2], '~') . '\\[\\](.*)\\}~sU', $file, $matches2)) {
					preg_match_all('~PHP_(?:FALIAS|ME_MAPPING|ME)\\((\\w+)\\s*,\\s*(\\w+)~', $matches2[1], $matches2, PREG_SET_ORDER);
					foreach ($matches2 as $match2) {
						$classes[$match2[1]] = $match[1];
						$method_names[strtolower($match2[2])] = strtolower("$match[1]::$match2[1]");
					}
				}
			}
		}
		
		foreach ($files as $filename => $file) {
			$file = preg_replace('~//[^\n]*|/\*.*?\*/~s', '', $file); // TODO: Respect strings. Remove #ifdef 0.
			preg_match_all('~^(?:static )?(?:ZEND|PHP)(_NAMED)?_(?:FUNCTION|METHOD)\\(([^)]+)~m', $file, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$function = trim($match[1] ? $aliases[$match[2]] : $match[2]);
				if (preg_match('~^(.*\\S)\\s*,\\s*(.+)~', $function, $match)) {
					$function = (isset($classes[$match[1]]) ? $classes[$match[1]] : preg_replace_callback('~_(.)~', function ($match) {
						return strtoupper($match[1]);
					}, $match[1])) . "::$match[2]";
				}
				if (isset($method_names[$function])) {
					$function = $method_names[$function];
				}
				$return++;
				if (!isset($versions[$function][$major])) {
					$versions[$function][$major] = array($version); // Min.
				}
				$versions[$function][$major][1] = $version; // Max.
				// TODO: Gaps are ignored.
			}
		}
	}
	return $return;
}

$maxes = array(); // major => version
$nexts = array();
$prev = "";
foreach ($tags as $version) {
	if ($prev) {
		$nexts[$prev] = $version;
	}
	$prev = $version;
	$major = substr($version, 0, 1);
	$maxes[$major] = $version;
}

$existing = array();
$xml = simplexml_load_file(__DIR__ . "/../.manual.xml");
foreach ($xml->getDocNamespaces() as $prefix => $namespace) {
	$xml->registerXPathNamespace(($prefix ? $prefix : "_"), $namespace);
}
foreach ($xml->xpath("//_:refname") as $refname) {
	$existing[name($refname)] = '';
}
$xml = simplexml_load_file(__DIR__ . "/../version.xml");
foreach ($xml->function as $function) {
	$existing[name($function['name'])] = $function['from']->__toString();
}

function name($name) {
	// Copied from PhD.
	return str_replace(
		array('::', '->', '__', '_', '$'),
		array('-',  '-',  '-',  '-', ''),
		strtolower($name));
}

ksort($versions);
$wrong = "";
echo "Missing versions:\n";
foreach ($versions as $function => $val) {
	$function = strtr($function, array(
		'##win32## ' => 'w32api::',
		'(oci_globals.v)' => 'OCI',
		'ceSimpleXML' => 'SimpleXML',
		'spl ## Array' => 'ArrayObject',
		'spl ## ' => '',
		'reflection::function_' => 'ReflectionFunctionAbstract',
		'tnm_ ##' => 'tidyNode::',
	));
	$print = array();
	foreach ($val as $major => $pair) {
		list($min, $max) = $pair;
		$printMin = ($min != "$major.0.0" ? " >= $min" : "");
		$printMax = ($max != $maxes[$major] ? " < $nexts[$max]" : "");
		$print[] = "PHP $major$printMin$printMax";
	}
	if (isset($existing[name($function)])) {
		if (!$existing[name($function)]) {
			echo " <function name=\"$function\" from=\"" . htmlspecialchars(implode(", ", $print)) . "\"/>\n";
		} elseif (preg_replace('~^PHP 4[^,]*, |, PECL .*~', '', $existing[name($function)]) != implode(", ", $print)) {
			$wrong .= " <function name=\"$function\" from=\"" . htmlspecialchars(implode(", ", $print)) . "\"/> <!-- " . $existing[name($function)] . " -->\n";
		}
	}
}
echo "\nWrong versions:\n$wrong";

function rglob($pattern, $dir = "") {
	foreach (glob($dir . $pattern) as $filename) {
		yield $filename;
	}
	foreach (glob("$dir*", GLOB_ONLYDIR) as $subdir) {
		foreach (rglob($pattern, "$subdir/") as $filename) {
			yield $filename;
		}
	}
}
