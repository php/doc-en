<?php
/*  
  +----------------------------------------------------------------------+
  | PHP Version 5                                                        |
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
  | Authors:    Felipe Pena <felipe@php.net>                             |
  +----------------------------------------------------------------------+
 
  $Id$
*/

if (!(extension_loaded('reflection') & extension_loaded('pcre'))) {
	die("To run this script is necessary the Reflection and PCRE extensions\n");
}

/* Constants */
define('DOC_METHOD', 	1<<0);
define('DOC_PROPERTY', 	1<<1);
define('DOC_CLASS', 	1<<2);
define('DOC_EXTENSION',	1<<3);
define('DOC_FUNCTION',	1<<4);

/* Templates */
$TEMPLATE = array(
	DOC_METHOD 	 => 'method.tpl',
	DOC_PROPERTY => 'property.tpl',
	DOC_CLASS 	 => 'class.tpl',
	DOC_FUNCTION => 'function.tpl'
);

/* Default files for extensions */
$DOC_EXT = array(
	'book.xml' 		 => 'book.tpl',
	'setup.xml' 	 => 'setup.tpl',
	'constants.xml'  => 'constants.tpl',
	'configure.xml'  => 'configure.tpl',
	'examples.xml'	 => 'examples.tpl',
	'reference.xml'  => 'reference.tpl',
	'ini.xml'		 => 'ini.tpl',
	'versions.xml'	 => 'versions.tpl'
);

function usage() { /* {{{ */
	$v = PHP_VERSION;
	print <<<USAGE
	Usage: 
		php docgen.php <options>

	Example options:
		-e dom                                (an entire extension)
		-f preg_replace                       (a single function)
		-c simplexmlelement -m xpath -m asxml (a couple class methods)

	Notes:
		Long options are supported with PHP 5.3.0+, and you use $v
		Be sure the desired extension to document is available (compiled) into PHP

	Options:
		-c,--class 	-- class name
		-e,--extension	-- extension name
		-h,--help	-- show this help
		-m,--method	-- method name (require -c)
		-o,--output	-- output dir
		-v,--version	-- show the version
		-V,--verbose 	-- disable show progress


USAGE;
}
/* }}} */

function add_warning($err_msg) { /* {{{ */
	global $WARNING, $INFO;

	$WARNING[$INFO['actual_file']][] = $err_msg;
}
/* }}} */

function create_markup_to_modifiers(ReflectionMethod $method) { /* {{{ */
	if ($method->isConstructor()) {
		return '';
	}

	$modifiers = Reflection::getModifierNames($method->getModifiers());
	$markup = '';

	if ($modifiers) {
		foreach ($modifiers as $modifier) {
			$markup .= '<modifier>'. $modifier .'</modifier> ';
		}
	}

	return $markup;
}
/* }}} */

function format_id($name) { /* {{{ */
	return preg_replace(array('/[^[:alnum:]]/', '/^-+/'), array('-', ''), strtolower($name));
}
/* }}} */

function format_filename($name) { /* {{{ */
        $name = strtolower(trim($name));
        $name = ltrim($name, '_');
        $name = str_replace('_', '-', $name);
        return $name;
}
/* }}} */

function format_config($name) { /* {{{ */
	return preg_replace('/_/', '-', $name);
}
/* }}} */

function get_ident_size($placeholder, $content) { /* {{{ */
	preg_match('/^([[:blank:]]*)\{'. $placeholder .'\}/m', $content, $match);
	return isset($match[1]) ? strlen($match[1]) : false;
}
/* }}} */

function save_file($filename, $content) { /* {{{ */
	global $OPTION;

	file_put_contents($filename, $content);
	if ($OPTION['verbose']) {
		printf("%s\n", $filename);
	}
}
/* }}} */

function get_type_by_string($str) { /* {{{ */
	if (is_numeric($str)) {
		if ($str && intval($str) == $str) {
			return 'integer';
		} else if ($str && floatval($str) == $str) {
			return 'float';
		} else {
			return 'integer';
		}
	} else {
		return 'string';
	}
}
/* }}} */

function create_dir($path) { /* {{{ */
	global $OPTION;

	if (!file_exists($path)) {
		if ($OPTION['verbose']) {
			printf("- Creating directory `%s'\n", $path);
		}
		if (!mkdir($path, 0777)) {
			add_warning("chmod: Permission denied `{$path}'");
			return;
		}
	}
}
/* }}} */

function create_markup_to_params(array $params, $ident) { /* {{{ */
	$count = 1;
	foreach ($params as $param) {
		/* Parameter type */
		if (preg_match('/(\w+) \$/', (string) $param, $match)) {
			/* 'array or NULL' is used for array type-hint */
			$type = $match[1] == 'NULL' ? 'array' : $match[1];
		} else {
			$type = 'string';
			if (!$param->getName()) {
				add_warning(sprintf("Parameter name not found, param%d used", $count));
			}
			add_warning(sprintf("Type hint for parameter `%s' not found, 'string' used", ($param->getName() ? $param->getName() : $count)));
		}

		$markup .= sprintf("%s<methodparam%s><type>%s</type><parameter%s>%s</parameter></methodparam>\n",
			($markup ? str_repeat(' ', $ident) : ''),
			($param->isOptional() ? ' choice="opt"' : ''),
			$type,
			($param->isPassedByReference() ? ' role="reference"' : ''),
			($param->getName() ? $param->getName() : 'param'. $count));

		$count++;
	}

	return rtrim($markup, "\n");
}
/* }}} */

function global_check($content) { /* {{{ */
	global $INFO;

	if (!$INFO['actual_extension']) {
		$INFO['actual_extension'] = 'EXTENSION_NAME_HERE';
	}

	/* {EXT_NAME_ID} */
	$content = preg_replace('/\{EXT_NAME_ID\}/', $INFO['actual_extension'], $content);

	/* {EXT_NAME} */
	$content = preg_replace('/\{EXT_NAME\}/', ucwords($INFO['actual_extension']), $content);

	return $content;
}
/* }}} */

function create_markup_to_parameter_section(Reflector $obj, $content) { /* {{{ */
	/* {PARAMETERS} */
	if ($obj->getNumberOfParameters()) {
		$ident = get_ident_size('PARAMETERS', $content);

		$parameters = $obj->getParameters();
		$content = preg_replace('/\{PARAMETERS\}/', create_markup_to_params($parameters, $ident), $content);

		/* {PARAMETERS_DESCRIPTION} */
		if ($ident = get_ident_size('PARAMETERS_DESCRIPTION', $content)) {
			$count = 1;

			$markup  = "<para>\n";
			$markup .= str_repeat(' ', $ident + 1) ."<variablelist>\n";
			foreach ($parameters as $param) {
				$markup .= str_repeat(' ', $ident + 2) ."<varlistentry>\n";
				$markup .= str_repeat(' ', $ident + 3) .'<term><parameter>'. ($param->getName() ? $param->getName() : 'param'. $count) ."</parameter></term>\n";
				$markup .= str_repeat(' ', $ident + 3) ."<listitem>\n";
      			$markup .= str_repeat(' ', $ident + 4) ."<para>\n";
       			$markup .= str_repeat(' ', $ident + 5) ."Description...\n";
       			$markup .= str_repeat(' ', $ident + 4) ."</para>\n";
     			$markup .= str_repeat(' ', $ident + 3) ."</listitem>\n";
    			$markup .= str_repeat(' ', $ident + 2) ."</varlistentry>\n";

    			$count++;
			}
			$markup .= str_repeat(' ', $ident + 1) ."</variablelist>\n";
			$markup .= str_repeat(' ', $ident) ."</para>\n";

			$content = preg_replace('/\{PARAMETERS_DESCRIPTION\}/', $markup, $content, 1);
		}
	} else {
		$content = preg_replace('/\{PARAMETERS\}/', '<void />', $content, 1);
		$content = preg_replace('/\{PARAMETERS_DESCRIPTION\}/', '&no.function.parameters;', $content, 1);
	}

	return $content;
}
/* }}} */

function gen_function_markup(ReflectionFunction $function, $content) { /* {{{ */
	/* {FUNCTION_NAME_ID} */
	$content = preg_replace('/\{FUNCTION_NAME_ID\}/', format_id($function->getName()), $content);

	/* {FUNCTION_NAME} */
	$content = preg_replace('/\{FUNCTION_NAME\}/', $function->getName(), $content);

	/* {PARAMETERS}, {PARAMETERS_DESCRIPTION} */
	$content = create_markup_to_parameter_section($function, $content);

	return $content;
}
/* }}} */

function gen_method_markup(ReflectionMethod $method, $content) { /* {{{ */
	/* {PARAMETER}, {PARAMETERS_DESCRIPTION} */
	$content = create_markup_to_parameter_section($method, $content);

	/* {CLASS_NAME_ID} */
	$content = preg_replace('/\{CLASS_NAME_ID\}/', format_id($method->class), $content);

	/* {METHOD_NAME_ID} */
	$content = preg_replace('/\{METHOD_NAME_ID\}/', format_id($method->name), $content);

	/* {FULL_METHOD_NAME} */
	$content = preg_replace('/\{FULL_METHOD_NAME\}/', $method->class .'::'. $method->name, $content);

	/* {METHOD_NAME} */
	$content = preg_replace('/\{METHOD_NAME\}/', $method->name, $content);

	/* {MODIFIERS} */
	$content = preg_replace('/\{MODIFIERS\}/', create_markup_to_modifiers($method), $content, 1);

	/* {RETURN_TYPE} */
	if (!$method->isConstructor()) {
		$content = preg_replace('/\{RETURN_TYPE\}/', '<type>void</type>', $content, 1);
	} else {
		$content = preg_replace('/\{RETURN_TYPE\}/', '', $content, 1);
	}

	return $content;
}
/* }}} */

function gen_class_markup(ReflectionClass $class, $content) { /* {{{ */
	$id = format_id($class->getName());

	/* {CLASS_NAME} */
	$content = preg_replace('/\{CLASS_NAME\}/', $class->getName(), $content);

	/* {CLASS_NAME_ID} */
	$content = preg_replace('/{CLASS_NAME_ID\}/', $id, $content);

	/* {EXTENDS} */
	if ($parent = $class->getParentClass()) {
		$ident = get_ident_size('EXTENDS', $content);

		$markup  = "\n";
		$markup .= str_repeat(' ', $ident) . "<ooclass>\n";
	  	$markup .= str_repeat(' ', $ident + 1) ."<modifier>extends</modifier>\n";
	  	$markup .= str_repeat(' ', $ident + 1) .'<classname>'. $parent->getName() ."</classname>\n";
	 	$markup .= str_repeat(' ', $ident) .'</ooclass>';

		$content = preg_replace('/\{EXTENDS\}/', $markup, $content, 1);
	} else {
		$content = preg_replace('/^\s*\{EXTENDS\}.*?\n/m', '', $content, 1);
	}

	/* {IMPLEMENTS} */
	if ($interfaces = $class->getInterfaces()) {
		$ident = get_ident_size('IMPLEMENTS', $content);

		$markup = "\n";
		foreach ($interfaces as $interface) {
			$markup .= str_repeat(' ', $ident) ."<oointerface>\n";
			$markup .= str_repeat(' ', $ident + 1) .'<interfacename>'. $interface->getName() ."</interfacename>\n";
			$markup .= str_repeat(' ', $ident) ."</oointerface>\n\n";
		}

		$content = preg_replace('/\{IMPLEMENTS\}/', rtrim($markup, "\n"), $content, 1);
	} else {
		$content = preg_replace('/^\s*\{IMPLEMENTS\}.*?\n/m', '', $content, 1);
	}

	/* {CONSTANTS_LIST} */
	if ($constants = $class->getConstants()) {
		$ident = get_ident_size('CONSTANTS_LIST', $content);
		$markup = "<classsynopsisinfo role=\"comment\">Constants</classsynopsisinfo>\n";

		foreach ($constants as $constant => $value) {
			$markup .= str_repeat(' ', $ident) ."<fieldsynopsis>\n";
			$markup .= str_repeat(' ', $ident + 1) ."<modifier>const</modifier>\n";
			$markup .= str_repeat(' ', $ident + 1) .'<type>'. gettype($value) ."</type>\n";
      		$markup .= str_repeat(' ', $ident + 1) .'<varname linkend="'. $id .'.constants.'. format_id($constant) .'">'. $class->getName() .'::'. $constant ."</varname>\n";
      		$markup .= str_repeat(' ', $ident + 1) .'<initializer>'. $value ."</initializer>\n";
     		$markup .= str_repeat(' ', $ident) ."</fieldsynopsis>\n";
     	}

     	$content = preg_replace('/\{CONSTANTS_LIST\}/', $markup, $content, 1);
	} else {
		$content = preg_replace('/^\s*\{CONSTANTS_LIST\}.*?\n/m', '', $content, 1);
	}

	/* {CONSTANTS} */
	if ($constants) {
		$ident = get_ident_size('CONSTANTS', $content);

		$markup  = "\n<!-- {{{ ". $class->getName() ." constants -->\n";
		$markup .= str_repeat(' ', $ident) .'<section xml:id="'. $id .".constants\">\n";
		$markup .= str_repeat(' ', $ident + 1) ."&reftitle.constants;\n";
   		$markup .= str_repeat(' ', $ident + 1) .'<section xml:id="'. $id .".constants.types\">\n";
   		$markup .= str_repeat(' ', $ident + 2) .'<title>'. $class->getName() ." Node Types</title>\n";
    	$markup .= str_repeat(' ', $ident + 2) ."<variablelist>\n\n";

		foreach ($constants as $constant => $value) {
     		$markup .= str_repeat(' ', $ident + 3) .'<varlistentry xml:id="'. $id .".constants.none\">\n";
     		$markup .= str_repeat(' ', $ident + 4) .'<term><constant>'. $class->getName() .'::'. $constant ."</constant></term>\n";
      		$markup .= str_repeat(' ', $ident + 4) ."<listitem>\n";
       		$markup .= str_repeat(' ', $ident + 5) ."<para>Description here...</para>\n";
      		$markup .= str_repeat(' ', $ident + 4) ."</listitem>\n";
     		$markup .= str_repeat(' ', $ident + 3) ."</varlistentry>\n\n";
		}

		$markup .= str_repeat(' ', $ident + 2) ."</variablelist>\n";
  		$markup .= str_repeat(' ', $ident) ."</section>\n";
  		$markup .= "<!-- }}} -->\n";

  		$content = preg_replace('/\{CONSTANTS\}/', $markup, $content, 1);
	} else {
		$content = preg_replace('/^\s*\{CONSTANTS\}.*?\n/m', '', $content, 1);
	}


	/* {PROPERTIES_LIST} */
	if ($properties = $class->getProperties()) {
		$ident = get_ident_size('PROPERTIES_LIST', $content);

		$markup = "<classsynopsisinfo role=\"comment\">Properties</classsynopsisinfo>\n";
		foreach ($properties as $property) {
			/* Don't get inherited properties */
			if ($property->getDeclaringClass()->name != $class->name) {
				continue;
			}

			/* Get the modifier */
			preg_match('/(\w+) \$/', (string) $property, $match);

			$markup .= str_repeat(' ', $ident) ."<fieldsynopsis>\n";
			$markup .= str_repeat(' ', $ident + 1) .'<modifier>'. $match[1] ."</modifier>\n";
			$markup .= str_repeat(' ', $ident + 1) .'<varname linkend="'. $id .'.props.'. $property->getName() .'">'. $property->getName() ."</varname>\n";
			$markup .= str_repeat(' ', $ident) ."</fieldsynopsis>\n";
		}

		$content = preg_replace('/\{PROPERTIES_LIST\}/', $markup, $content, 1);
	} else {
		$content = preg_replace('/^\s*\{PROPERTIES_LIST\}.*?\n/m', '', $content, 1);
	}

	/* {PROPERTIES} */
	if ($properties) {
		$ident = get_ident_size('PROPERTIES', $content);

		$markup  = "\n<!-- {{{ ". $class->getName() ." properties -->\n";
		$markup .= str_repeat(' ', $ident) ."<section xml:id=\"". $id .".props\">\n";
		$markup .= str_repeat(' ', $ident + 1) ."&reftitle.properties;\n";
		$markup .= str_repeat(' ', $ident + 1) ."<variablelist>\n";

		foreach ($properties as $property) {
			$markup .= str_repeat(' ', $ident + 2) .'<varlistentry xml:id="'. $id .'.props.'. $property->getName() ."\">\n";
			$markup .= str_repeat(' ', $ident + 3) .'<term><varname>'. $property->getName() ."</varname></term>\n";
     		$markup .= str_repeat(' ', $ident + 3) ."<listitem>\n";
      		$markup .= str_repeat(' ', $ident + 4) ."<para>Prop description</para>\n";
     		$markup .= str_repeat(' ', $ident + 3) ."</listitem>\n";
    		$markup .= str_repeat(' ', $ident + 2) ."</varlistentry>\n";
		}

		$markup .= str_repeat(' ', $ident + 1) ."</variablelist>\n";
  		$markup .= str_repeat(' ', $ident) ."</section>\n";
  		$markup .= "<!-- }}} -->\n";

    	$content = preg_replace('/\{PROPERTIES\}/', $markup, $content, 1);
	} else {
		$content = preg_replace('/^\s*\{PROPERTIES\}.*?\n/m', '', $content, 1);
    }

	/* {PROPERTY_XINCLUDE} */
	$content = preg_replace('/\{PROPERTY_XINCLUDE\}/',
		"<xi:include xpointer=\"xmlns(db=http://docbook.org/ns/docbook) xpointer(id('class.". $id ."')/db:refentry/db:refsect1[@role='description']/descendant::db:fieldsynopsis[1])\" />\n",
		$content, 1);

	/* {METHOD_XINCLUDE} */
	$ident = get_ident_size('METHOD_XINCLUDE', $content);
	$content = preg_replace('/\{METHOD_XINCLUDE\}/',
		"\n". str_repeat(' ', $ident) . "<classsynopsisinfo role=\"comment\">Methods</classsynopsisinfo>\n".
		str_repeat(' ', $ident) ."<xi:include xpointer=\"xmlns(db=http://docbook.org/ns/docbook) xpointer(id('class.". $id ."')/db:refentry/db:refsect1[@role='description']/descendant::db:methodsynopsis[1])\" />",
		$content, 1);

	/* {INHERITED_XINCLUDE} */
	if ($parent) {
		$ident = get_ident_size('INHERITED_XINCLUDE', $content);
		$content = preg_replace('/\{INHERITED_XINCLUDE\}/',
			"\n". str_repeat(' ', $ident) ."<classsynopsisinfo role=\"comment\">Inherited methods</classsynopsisinfo>\n".
			str_repeat(' ', $ident) ."<xi:include xpointer=\"xmlns(db=http://docbook.org/ns/docbook) xpointer(id('class.". format_id($parent->getName()) ."')/db:refentry/db:refsect1[@role='description']/descendant::db:methodsynopsis[1])\" />\n",
			$content, 1);
	} else {
		$content = preg_replace('/^\s*\{INHERITED_XINCLUDE\}.*?\n/m', '', $content, 1);
	}

	return $content;
}
/* }}} */

function gen_extension_markup(ReflectionExtension $obj, $content, $xml_file) { /* {{{ */
	global $INFO;

	switch ($xml_file) {
		case 'ini.xml':
			if ($ini = $obj->getINIEntries()) {
				$ident = get_ident_size('INI_ENTRIES', $content);

				$markup = "<tbody>\n";;
				$markup2 = '';
				foreach ($ini as $config => $value) {
					$markup .= str_repeat(' ', $ident + 1) ."<row>\n";
					$markup .= str_repeat(' ', $ident + 2) ."<entry>". $config ."</entry>\n";
					$markup .= str_repeat(' ', $ident + 2) ."<entry>". $value ."</entry>\n";
					$markup .= str_repeat(' ', $ident + 2) ."<entry>its PHP_INI_* value</entry>\n";
					$markup .= str_repeat(' ', $ident + 2) ."<entry>leave empty. this will be fiiled by an automatic script</entry>\n";
					$markup .= str_repeat(' ', $ident + 1) ."</row>\n";

					$markup2 .= ($markup2 ? str_repeat(' ', $ident) : '') ."<varlistentry xml:id=\"ini.". format_config($config) ."\">\n";
					$markup2 .= str_repeat(' ', $ident + 1) ."<term>\n";
					$markup2 .= str_repeat(' ', $ident + 2) ."<parameter>". $config ."</parameter>\n";
					$markup2 .= str_repeat(' ', $ident + 2) ."<type>". get_type_by_string($value) ."</type>\n";
					$markup2 .= str_repeat(' ', $ident + 1) ."</term>\n";
					$markup2 .= str_repeat(' ', $ident + 1) ."<listitem>\n";
					$markup2 .= str_repeat(' ', $ident + 2) ."<para>\n";
					$markup2 .= str_repeat(' ', $ident + 3) ."Description here...\n";
					$markup2 .= str_repeat(' ', $ident + 2) ."</para>\n";
    				$markup2 .= str_repeat(' ', $ident + 1) ."</listitem>\n";
   					$markup2 .= str_repeat(' ', $ident) ."</varlistentry>\n";
				}
				$markup .= "</tbody>";

				/* {INI_ENTRIES} */
				$content = preg_replace('/\{INI_ENTRIES\}/', $markup, $content, 1);

				/* {INI_ENTRIES_DESCRIPTION} */
				$content = preg_replace('/\{INI_ENTRIES_DESCRIPTION\}/', $markup2, $content, 1);

			} else {
				return false; /* Abort */
			}
		break;

		case 'constants.xml':
			if ($constants = $obj->getConstants()) {
				$ident = get_ident_size('CONSTANTS', $content);

				$markup  = "&extension.constants;\n";
				$markup .= str_repeat(' ', $ident) ."<para>\n";
				$markup .= str_repeat(' ', $ident + 1) ."<variablelist>\n";

				foreach ($constants as $name => $value) {
					$markup .= str_repeat(' ', $ident + 2) ."<varlistentry>\n";
					$markup .= str_repeat(' ', $ident + 3) ."<term>\n";
					$markup .= str_repeat(' ', $ident + 4) ."<constant>". $name ."</constant>\n";
					$markup .= str_repeat(' ', $ident + 4) ."(<type>". gettype($value) ."</type>)\n";
					$markup .= str_repeat(' ', $ident + 3) ."</term>\n";
					$markup .= str_repeat(' ', $ident + 3) ."<listitem>\n";
					$markup .= str_repeat(' ', $ident + 4) ."<simpara>\n";
					$markup .= str_repeat(' ', $ident + 4) ."</simpara>\n";
					$markup .= str_repeat(' ', $ident + 3) ."</listitem>\n";
					$markup .= str_repeat(' ', $ident + 2) ."</varlistentry>\n";
				}

				$markup .= str_repeat(' ', $ident + 1) ."</variablelist>\n";
				$markup .= str_repeat(' ', $ident) ."</para>";

				$content = preg_replace('/\{CONSTANTS\}/', $markup, $content, 1);
			} else {
				$content = preg_replace('/\{CONSTANTS\}/', '&no.constants;', $content, 1);
			}
		break;
		
		case 'versions.xml':
			$markup = "<!-- Functions -->\n";
			/* Function list */
			if ($functions = $obj->getFunctions()) {
				foreach ($functions as $function) {
					$markup .= " <function name='". strtolower($function->getName()) ."' from='PHP 5 &gt;= 5.2.0'/>\n";
				}
			}
			$markup .= " <!-- Methods -->\n";
			/* Method list */
			if ($classes = $obj->getClasses()) {
				foreach ($classes as $class) {
					foreach ($class->getMethods() as $method) {
						$markup .= " <function name='". strtolower($class->name .'::'. $method->getName()) ."' from='PHP 5 &gt;= 5.2.0'/>\n";
					}
				}
			}
			$content = preg_replace('/\{VERSIONS\}/', rtrim($markup), $content);
		break;
	}

	return $content;
}
/* }}} */

function get_extension_name($string) { /* {{{ */
	preg_match('/<[^:]+:([^>]+)/', $string, $match);
	return isset($match[1]) ? format_id($match[1]) : false;
}
/* }}} */

function write_doc(Reflector $obj, $type) { /* {{{ */
	global $OPTION, $INFO, $TEMPLATE, $DOC_EXT;

	switch ($type) {
		case DOC_EXTENSION:
			foreach ($DOC_EXT as $xml_file => $tpl_file) {
				$filename = $OPTION['output'] .'/'. format_filename($xml_file);
				$INFO['actual_file'] = $filename;

				$content = file_get_contents(dirname(__FILE__) .'/'. $tpl_file);
				if ($content = gen_extension_markup($obj, $content, $xml_file)) {
					save_file($filename, global_check($content));
				}
			}
			break;

		/* Methods */
		case DOC_METHOD:
			$path = $OPTION['output'] .'/'. strtolower($obj->class);
			$filename = $path .'/'. format_filename($obj->name) .'.xml';

			create_dir($path);

			$INFO['actual_file'] = $filename;
			$content = file_get_contents(dirname(__FILE__) .'/'. $TEMPLATE[$type]);
			$content = gen_method_markup($obj, $content);

			save_file($filename, global_check($content));
		break;

		/* Properties */
		case DOC_PROPERTY:
			/* Doesn't exists separated file documenting property, actually
			 * they are documented in DOC_METHOD */
		break;

		/* Classes */
		case DOC_CLASS:
			$path = $OPTION['output'];
			$filename = $path .'/'. format_filename($obj->getName()) .'.xml';

			$INFO['actual_file'] = $filename;
			$content = file_get_contents(dirname(__FILE__) .'/'. $TEMPLATE[$type]);
			$content = gen_class_markup($obj, $content);

			/* classname.xml */
			save_file($filename, global_check($content));
		break;

		case DOC_FUNCTION:
			$path = $OPTION['output'] .'/functions';
			$filename = $path .'/'. format_filename($obj->getName()) .'.xml';
			$INFO['actual_file'] = $filename;

			create_dir($path);

			$content = file_get_contents(dirname(__FILE__) .'/'. $TEMPLATE[$type]);
			$content = gen_function_markup($obj, $content);

			save_file($filename, global_check($content));
		break;
	}
}
/* }}} */

function gen_docs($name, $type) {	/* {{{ */
	global $OPTION, $INFO;

	if ($type & DOC_EXTENSION) {
		try {
			$extension = new ReflectionExtension($name);

			$INFO['actual_extension'] = format_id($name);

			write_doc($extension, DOC_EXTENSION);

			foreach ($extension->getClasses() as $class) {
				gen_docs($class->name, DOC_CLASS);
			}

			foreach ($extension->getFunctions() as $function) {
				gen_docs($function->name, DOC_FUNCTION);
			}
		} catch (Exception $e) {
			die('Error: '. $e->getMessage() ."\n");
		}
	} else if ($type & DOC_FUNCTION) {
		try {
			$function = new ReflectionFunction($name);

			if (!$INFO['actual_extension']) {
				if ($extname = get_extension_name((string) $function)) {
					$INFO['actual_extension'] = $extname;
				} else {
					add_warning("The function {$name} has not information in Reflection about their extension");
				}
			}

			write_doc($function, DOC_FUNCTION);
		} catch (Exception $e) {
			die('Error: '. $e->getMessage() ."\n");
		}
	} else if ($type & DOC_METHOD) {
		try {
			$class = new ReflectionClass($OPTION['class']);

			if (!$INFO['actual_extension']) {
				if ($extname = get_extension_name((string) $class)) {
					$INFO['actual_extension'] = $extname;
				} else {
					add_warning("The class {$name} has not information in Reflection about their extension");
				}
			}

			foreach ($class->getMethods() as $method) {
				/* Don't get the inherited methods */
				if ($method->getDeclaringClass()->name == $class->name &&
					((is_array($OPTION['method']) && in_array(strtolower($method->getName()), $OPTION['method']))
					|| $OPTION['method'] == strtolower($method->getName()))) {
					write_doc($method, DOC_METHOD);
				}
			}
		} catch (Exception $e) {
			die('Error: '. $e->getMessage() ."\n");
		}
	} else if ($type & DOC_CLASS) {
		try {
			$class = new ReflectionClass($name);

			if (!$INFO['actual_extension']) {
				if ($extname = get_extension_name((string) $class)) {
					$INFO['actual_extension'] = $extname;
				} else {
					add_warning("The class {$name} has not information in Reflection about their extension");
				}
			}
			write_doc($class, DOC_CLASS);

			foreach ($class->getMethods() as $method) {
				/* Don't get the inherited methods */
				if ($method->getDeclaringClass()->name == $class->name) {
					write_doc($method, DOC_METHOD);
				}
			}
		} catch (Exception $e) {
			die('Error: '. $e->getMessage() ."\n");
		}
	}
}
/* }}} */

$OPTION  = array();
$INFO 	 = array('actual_extension' => false);
$WARNING = array();
$OPTION['extension'] = NULL;
$OPTION['method']	 = NULL;
$OPTION['class']	 = NULL;
$OPTION['function']  = NULL;
$OPTION['output']	 = getcwd();
$OPTION['verbose']   = true;
$OPTION['quiet']	 = false;

$arropts = array(
	'verbose' 		=> 'v',  /* version */
	'version' 		=> 'V',  /* verbose */
	'quiet'   		=> 'q',  /* quiet */
	'help'	  		=> 'h',  /* help */
	'output:' 		=> 'o:', /* output dir */
	'class:'  		=> 'c:', /* classname */
	'extension:' 	=> 'e:', /* extension */
	'function:' 	=> 'f:', /* function */
	'method:' 		=> 'm:'  /* method */
);

$options = @getopt(implode($arropts), array_keys($arropts));

if (!$options) {
	usage();
	exit;
}

foreach ($options as $opt => $value) {
	switch ($opt) {
		case 'v':
		case 'version':
			printf("%s\n", '$Revision$');
			break;
		case 'V':
		case 'verbose':
			$OPTION['verbose'] = false;
			break;
		case 'h':
		case 'help':
			usage();
			break;
		case 'e':
		case 'extension':
			$OPTION['extension'] = $value;
			break;
		case 'f':
		case 'function':
			$OPTION['function'] = $value;
			break;
		case 'm':
		case 'method':
			if (!array_key_exists('c', $options) && !array_key_exists('class', $options)) {
				die("Error: The class name should be supplied (i.e. -c classname)\n");
			}
			$OPTION['method'] = is_array($value) ? array_map('strtolower', $value) : strtolower($value);
			break;
		case 'c':
		case 'class':
			$OPTION['class'] = $value;
			break;
		case 'o':
		case 'output':
			if (!file_exists($value) || !is_writable($value)) {
				die("Error: The directory output must exists and writable\n");
			}
			$OPTION['output'] = $value;
			break;
		case 'q':
		case 'quiet':
			$OPTION['quiet'] = true;
			break;
	}
}

if (!empty($OPTION['extension'])) {
	if (is_array($OPTION['extension'])) {
		foreach ($OPTION['extension'] as $extension) {
			gen_docs($extension, DOC_EXTENSION);
		}
	} else {
		gen_docs($OPTION['extension'], DOC_EXTENSION);
	}
}

if (!empty($OPTION['function'])) {
	gen_docs($OPTION['function'], DOC_FUNCTION);
}

if (!empty($OPTION['method'])) {
	gen_docs($OPTION['method'], DOC_METHOD);
}

if (empty($OPTION['method']) && !empty($OPTION['class'])) {
	if (is_array($OPTION['class'])) {
		foreach ($OPTION['class'] as $classname) {
			gen_docs($classname, DOC_CLASS);
		}
	} else {
		gen_docs($OPTION['class'], DOC_CLASS);
	}
}

/* Warnings */
if (!empty($WARNING) && !$OPTION['quiet']) {
	print "\nWarnings:\n";
	foreach ($WARNING as $file => $messages) {
		printf("- %s\n", $file);
		foreach ($messages as $message) {
			printf("\t-- %s\n", $message);
		}
	}
}
