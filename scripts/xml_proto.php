<?php
/*
# +----------------------------------------------------------------------+
# | PHP Version 4                                                        |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997-2002 The PHP Group                                |
# +----------------------------------------------------------------------+
# | This source file is subject to version 2.02 of the PHP licience,     |
# | that is bundled with this package in the file LICENCE and is         |
# | avalible through the world wide web at                               |
# | http://www.php.net/license/2_02.txt.                                 |
# | If uou did not receive a copy of the PHP license and are unable to   |
# | obtain it through the world wide web, please send a note to          |
# | license@php.net so we can mail you a copy immediately                |
# +----------------------------------------------------------------------+
# | Authors:   Brad House <bradmssw@php.net>                             |
# +----------------------------------------------------------------------+
#
# $Id$
*/

/*
  This program generates XML files for functions implemented in a PHP
  extension with documented protos.Pass the PHP extension.c file to
  the script, and it will write out a number of files based on the
  protos in the C source.
*/

$funclist=array();
$num_funcs=0;

function new_function()
{
  global $funclist, $num_funcs;
  $funclist[$num_funcs]["args"]=array();
  $funclist[$num_funcs]["num_args"]=0;
  $num_funcs++;
  return($num_funcs-1);
}

function fix_name($name)
{
  $ret="";
  $len=strlen($name);

  for ($i=0; $i<$len; $i++) {
    $c=substr($name, $i, 1);
    if ($c == '_') {
      $ret .= '-';
    } else {
      $ret .= $c;
    }
  }
  return($ret);
}

function function_add_name($num, $name)
{
  global $funclist, $num_funcs;

  $funclist[$num]["function_name"]=$name;
  $funclist[$num]["function_name_fix"]=fix_name($name);
  return(1);
}

function function_add_type($num, $type)
{
  global $funclist, $num_funcs;
  $funclist[$num]["function_type"]=$type;
  return(1);
}

function function_add_purpose($num, $purpose)
{
  global $funclist, $num_funcs;
  $funclist[$num]["purpose"]=$purpose;
  return(1);
}

function function_add_arg($num, $type, $argname)
{
  global $funclist, $num_funcs;

  $num_args=$funclist[$num]["num_args"];
  $funclist[$num]["args"][$num_args]["type"]=$type;
  $funclist[$num]["args"][$num_args]["variable"]=$argname;
  $funclist[$num]["num_args"]++;

  return(1);
}

function write_xml_files()
{
  global $funclist, $num_funcs;

  $filename="";
  $fp=0;

  for ($i=0; $i<$num_funcs; $i++) {
    $filename= $funclist[$i]["function_name_fix"] . ".xml";
    $fp=fopen($filename, "wb");
    if (!$fp) {
      echo "Failed writing: $filename\n";
      continue;
    }
    $fixname=$funclist[$i]["function_name_fix"];
    $funcname=$funclist[$i]["function_name"];
    $purpose=$funclist[$i]["purpose"];
    $functype=$funclist[$i]["function_type"];

    fwrite($fp, "<?xml version='1.0' encoding='iso-8859-1'?>\n" .
               "<!-- $Revision$ -->\n" .
               "  <refentry id=\"function." . $fixname . "\">\n" .
               "   <refnamediv>\n" .
               "    <refname>$funcname</refname>\n" .
               "    <refpurpose>$purpose</refpurpose>\n" .
               "   </refnamediv>\n" .
               "   <refsect1>\n" .
               "    <title>Description</title>\n" .
               "    <methodsynopsis>\n" .
               "     <type>$functype</type><methodname>$funcname</methodname>\n");

    for ($j=0; $j<$funclist[$i]["num_args"]; $j++) {
      $argtype=$funclist[$i]["args"][$j]["type"];
      $argname=$funclist[$i]["args"][$j]["variable"];
      fwrite($fp, "     <methodparam><type>$argtype</type><parameter>$argname</parameter></methodparam>\n");
    }
    if ($funclist[$i]["num_args"] == 0){
      fwrite($fp, "     <void/>\n");
    }

    fwrite($fp, "    </methodsynopsis>\n" .
	       "    <para>\n" .
               "     &warn.undocumented.func;\n" .
               "    </para>\n" .
               "   </refsect1>\n" .
               "  </refentry>\n" .
               "\n" .
               "<!-- Keep this comment at the end of the file\n" .
               "Local variables:\n" .
               "mode: sgml\n" .
               "sgml-omittag:t\n" .
               "sgml-shorttag:t\n" .
               "sgml-minimize-attributes:nil\n" .
               "sgml-always-quote-attributes:t\n" .
               "sgml-indent-step:1\n" .
               "sgml-indent-data:t\n" .
               "indent-tabs-mode:nil\n" .
               "sgml-parent-document:nil\n" .
               "sgml-default-dtd-file:\"../../../../manual.ced\"\n" .
               "sgml-exposed-tags:nil\n" .
               "sgml-local-catalogs:nil\n" .
               "sgml-local-ecat-files:nil\n" .
               "End:\n" .
               "vim600: syn=xml fen fdm=syntax fdl=2 si\n" .
               "vim: et tw=78 syn=sgml\n" .
               "vi: ts=1 sw=1\n" .
               "-->\n");
    fclose($fp);
    echo "Wrote: $filename\n";
  }
  return(1);
}

function read_file($filename)
{
  $fp = fopen($filename, "rb");
  if ($fp == 0) return("");
  $buffer=fread($fp, filesize($filename));
  fclose($fp);
  return($buffer);
}

function parse_desc($func_num, $data)
{
  $len=0;
  $i=0;
  $c=0;
  $temp="";
  $temp_len=0;
  $spaces=0;

  $len=strlen($data);
  for ($i=0; $i<$len; $i++) {
    $c=substr($data, $i, 1);
    switch ($c) {
      case '\r':
      case '\n':
      case ' ':
        if (!$spaces) {
	  $spaces=1;
          $temp .= ' ';
	  $temp_len++;
        }
      break;

      default:
        if ($c != '\r' && $c != '\n') {
          $spaces=0;
	  $temp .= $c;
	  $temp_len++;
        }
      break;
    }
  }
  function_add_purpose($func_num, $temp);
  return(1);
}

function parse_proto($proto)
{
  $len=0;
  $i=0;
  $c=0;
  $done=0;
  $start=0;
  $func_number=-1;
  $got_proto_def=0;
  $got_proto_type=0;
  $got_proto_name=0;
  $got_arg_type=0;
  $start_args=0;
  $temp="";
  $temp2="";
  $temp_len=0;

  $len=strlen($proto);

  for ($i=0; $i<$len; $i++) {
    $c=substr($proto, $i, 1);
    switch ($c) {
      case '\r':
      case '\n':
      case ' ':
        if ($temp_len) {
	  if (!$got_proto_def) {
	    if (strcasecmp($temp, "proto") != 0) {
	      echo "Not a proper proto definition: $proto\n";
	      return(0);
	    } else {
	      $got_proto_def=1;
	    }
	  } else if (!$got_proto_type) {
	    $func_number=new_function();
            function_add_type($func_number, $temp);
	    $got_proto_type=1;
	  } else if (!$got_proto_name) {
            function_add_name($func_number, $temp);
	    $got_proto_name=1;
	  } else if ($start_args && !$got_arg_type) {
            $got_arg_type=1;
	    $temp2=$temp;
	  } else if ($start_args && $got_arg_type) {
	    $got_arg_type=0;
            function_add_arg($func_number, $temp2, $temp);
	    $temp2="";
	  }
	  $temp_len=0;
	  $temp="";
        }
      break;

      case '(':
        if ($got_proto_type && $got_proto_def &&!$got_proto_name) {
          function_add_name($func_number, $temp);
	  $temp="";
	  $temp_len=0;
	  $start_args=1;
	  $got_proto_name=1;
	} else {
	  echo "Not a proper proto definition -2: $proto\n";
	  return(0);
	}

      break;

      case ')':
        if ($start_args) {
	  if ($got_arg_type && $temp_len) {
	    function_add_arg($func_number, $temp2, $temp);
	    $temp="";
	    $temp_len=0;
	  }
          $done=1;
	} else {
	  echo "Not a proper proto definition -4: $proto\n";
	  return(0);
	}
      break;

      case ',':
        if ($start_args && $got_arg_type) {
	  $got_arg_type=0;
          function_add_arg($func_number, $temp2, $temp);
	  $temp2="";
	  $temp="";
	  $temp_len=0;
	} else {
	  echo "Not a proper proto definition -3: $proto\n";
	  return(0);
	}
      break;

      default:
        if ($c != '\r' && $c != '\n') {
          $temp .= $c;
	  $temp_len++;
	}
      break;
    }
    if ($done) {
      $start=$i+1;
      break;
    }
  }
  parse_desc($func_number, substr($proto, $start));
  return(1);
}

function parse_file($buffer)
{
  global $funclist, $num_funcs;

  $temp1="";
  $temp2="";
  $ptr="";
  $args="";

  $ptr=$buffer;
  while (1) {
    $temp1=strstr($ptr, "{{{");
    if ($temp1 == false) break;
    $temp2=strstr($temp1, "*/");
    if ($temp2 == false) break;
    $args=substr($temp1, 3, strlen($temp1)-strlen($temp2)-3);
    parse_proto($args);
    $ptr=$temp2;
  }
  return(1);
}

function create_xml_docs($filename)
{
  $contents=read_file($filename);
  if ($contents == false || $contents == "") {
    echo "Could not read $filename\n";
  }
  parse_file($contents);
  write_xml_files();
  return(1);
}

$myargc=$_SERVER["argc"];
$myargv=$_SERVER["argv"];

if ($myargc < 2) {
  echo "Usage: " . $myargv[0] . " <extension.c>\n";
  exit(1);
}

create_xml_docs($myargv[1]);
?>

