/* 
   +----------------------------------------------------------------------+
   | PHP Version 4                                                        |
   +----------------------------------------------------------------------+
   | Copyright (c) 1997-2002 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP licience,     |
   | that is bundled with this package in the file LICENCE and is         |
   | avalible through the world wide web at                               |
   | http://www.php.net/license/2_02.txt.                                 |
   | If uou did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world wide web, please send a note to          |
   | license@php.net so we can mail you a copy immediately                |
   +----------------------------------------------------------------------+
   | Authors: Brad House <bradmssw@php.net>                               |
   +----------------------------------------------------------------------+
 */ 

/* $Id$ */

/*
  This program generates XML files for functions implemented in a PHP
  extension with documented protos. Compile with this command line:
    
    gcc -Wall -O3 -o xml_proto xml_proto.c

  Then pass the php extension.c file to the new program, and it will
  write out a number of files based on the protos.
*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>

typedef struct XML_proto {
  char type[255];
  char variable[255];
} XML_proto;

typedef struct XML_functions {
  char function_type[255];
  char function_name[255];
  char function_name_fix[255];
  char purpose[1024];
  int num_args;
  XML_proto *args;
} XML_functions;

XML_functions *funclist=NULL;
int num_funcs=0;

int new_function()
{
  funclist=(XML_functions *)realloc(funclist, (num_funcs+1)*sizeof(XML_functions));
  funclist[num_funcs].function_name[0]=0;
  funclist[num_funcs].function_name_fix[0]=0;
  funclist[num_funcs].purpose[0]=0;
  funclist[num_funcs].num_args=0;
  funclist[num_funcs].args=NULL;
  num_funcs++;
  return(num_funcs-1);
}

char *fix_name(char *name)
{
  int len, i;
  char *ret=NULL;
  len=strlen(name);
  ret=(char *)malloc((len+1)*sizeof(char));
  memset(ret, 0, len+1);
  for (i=0; i<len; i++) {
    if (name[i] == '_') {
      ret[i]='-';
    } else {
      ret[i]=name[i];
    }
    ret[i+1]=0;
  }
  return(ret);
}

int function_add_name(int num, char *name)
{
  char *n;
  if (name != NULL) {
    strncpy(funclist[num].function_name, name, 255);
    n=fix_name(name);
    strncpy(funclist[num].function_name_fix, n, 255);
    free(n);
    return(1);
  }
  return(0);
}

int function_add_type(int num, char *type)
{
  if (type != NULL) {
    strncpy(funclist[num].function_type, type, 255);
    return(1);
  }
  return(0);
}

int function_add_purpose(int num, char *purpose)
{
  if (purpose != NULL) {
    strncpy(funclist[num].purpose, purpose, 1024);
    return(1);
  }
  return(0);
}

int function_add_arg(int num, char *type, char *argname)
{
  funclist[num].args=realloc(funclist[num].args, (funclist[num].num_args+1)*sizeof(XML_proto));
  funclist[num].args[funclist[num].num_args].type[0]=0;
  funclist[num].args[funclist[num].num_args].variable[0]=0;
  strncpy(funclist[num].args[funclist[num].num_args].type, type, 255);
  strncpy(funclist[num].args[funclist[num].num_args].variable, argname, 255);
  funclist[num].num_args++;
  return(1);
}

int write_xml_files()
{
  int i;
  int j;
  char filename[1024];
  FILE *fp;

  for (i=0; i<num_funcs; i++) {
    snprintf(filename, 1024, "%s.xml", funclist[i].function_name_fix);
    fp=fopen(filename, "wb");
    if (fp == NULL) {
      printf("Failed writing: %s\n", filename);
      continue;
    }
    fprintf(fp, "<?xml version='1.0' encoding='iso-8859-1'?>\n"
            "<!-- $Revision$ -->\n");
    fprintf(fp, "  <refentry id=\"function.%s\">\n",
            funclist[i].function_name_fix);
    fprintf(fp, "   <refnamediv>\n"
            "    <refname>%s</refname>\n"
            "    <refpurpose>%s</refpurpose>\n",
            funclist[i].function_name, funclist[i].purpose);
    fprintf(fp, "   </refnamediv>\n"
            "   <refsect1>\n"
            "    <title>Description</title>\n"
            "    <methodsynopsis>\n");
    fprintf(fp, "     <type>%s</type><methodname>%s</methodname>\n",
            funclist[i].function_type, funclist[i].function_name);
    
    for (j=0; j<funclist[i].num_args; j++) {
      fprintf(fp, "     <methodparam><type>%s</type><parameter>%s</parameter></methodparam>\n",
              funclist[i].args[j].type, funclist[i].args[j].variable);
    }
    if (funclist[i].num_args == 0){
      fprintf(fp, "     <void/>\n");
    }
        
    fprintf(fp, "    </methodsynopsis>\n"
//          "     &warn.experimental.func;\n"
	    "    <para>\n"
            "     &warn.undocumented.func;\n"
            "    </para>\n"
            "   </refsect1>\n"
            "  </refentry>\n"
            "\n"
            "<!-- Keep this comment at the end of the file\n"
            "Local variables:\n"
            "mode: sgml\n"
            "sgml-omittag:t\n"
            "sgml-shorttag:t\n"
            "sgml-minimize-attributes:nil\n"
            "sgml-always-quote-attributes:t\n"
            "sgml-indent-step:1\n"
            "sgml-indent-data:t\n"
            "indent-tabs-mode:nil\n"
            "sgml-parent-document:nil\n"
            "sgml-default-dtd-file:\"../../../../manual.ced\"\n"
            "sgml-exposed-tags:nil\n"
            "sgml-local-catalogs:nil\n"
            "sgml-local-ecat-files:nil\n"
            "End:\n"
            "vim600: syn=xml fen fdm=syntax fdl=2 si\n"
            "vim: et tw=78 syn=sgml\n"
            "vi: ts=1 sw=1\n"
            "-->\n");
    fclose(fp);
    printf("Wrote: %s\n", filename);
  }
  return(1);
}

long file_length(FILE *fp)
{
  long pos, len;
  pos=ftell(fp);
  fseek(fp, 0L, SEEK_END);
  len=ftell(fp);
  fseek(fp, pos, SEEK_SET);
  return(len);
}

char *read_file(char *filename)
{
  FILE *fp;
  char *buffer=NULL;
  long len;

  fp=fopen(filename, "rb");
  if (fp == NULL) return(NULL);
  len=file_length(fp);
  buffer=(char *)malloc((len+1)*sizeof(char));
  memset(buffer, 0, len+1);
  fread(buffer, len, sizeof(char), fp);
  fclose(fp);
  return(buffer);
}

char *midstr(char *string, long start, long len)
{
  char *ret=NULL;
  char *ptr=NULL;
  ret=(char *)malloc((len+1)*sizeof(char));
  ptr=string+start;
  memset(ret, 0, len+1);
  memcpy(ret, ptr, len);
  return(ret);
}

int parse_desc(int func_num, char *data)
{
  long len, i;
  int c;
  char temp[1024];
  int temp_len=0;
  int spaces=0;

  temp[0]=0;
  len=strlen(data);
  for (i=0; i<len; i++) {
    c=data[i];
    switch (c) {
      case '\r':
      case '\n':
      case ' ':
        if (!spaces) {
	  spaces=1;
          temp[temp_len]=' ';
	  temp_len++;
	  temp[temp_len]=0;
        }
      break;

      default:
        spaces=0;
	temp[temp_len]=c;
	temp_len++;
	temp[temp_len]=0;
      break;
    }
  }
  function_add_purpose(func_num, temp);
  return(1);
}

int parse_proto(char *proto)
{
  long len, i;
  int c;
  int done=0;
  int start=0;
  int func_number=-1;
  int got_proto_def=0;
  int got_proto_type=0;
  int got_proto_name=0;
  int got_arg_type=0;
  int start_args=0;
  char temp[1024];
  char temp2[1024];
  int temp_len=0;

  len=strlen(proto);
  temp[0]=0;

  for (i=0; i<len; i++) {
    c=proto[i];
    switch (c) {
      case '\r':
      case '\n':
      case ' ':
        if (temp_len) {
	  if (!got_proto_def) {
	    if (strcasecmp(temp, "proto") != 0) {
	      printf("Not a proper proto definition: %s\n", proto);
	      return(0);
	    } else {
	      got_proto_def=1;
	    }
	  } else if (!got_proto_type) {
	    func_number=new_function();
            function_add_type(func_number, temp);
	    got_proto_type=1;
	  } else if (!got_proto_name) {
            function_add_name(func_number, temp);
	    got_proto_name=1;
	  } else if (start_args && !got_arg_type) {
            got_arg_type=1;
	    strcpy(temp2, temp);
	  } else if (start_args && got_arg_type) {
	    got_arg_type=0;
            function_add_arg(func_number, temp2, temp);
	    temp2[0]=0;
	  }
	  temp_len=0;
	  temp[0]=0;
        }
      break;

      case '(':
        if (got_proto_type && got_proto_def &&!got_proto_name) {
          function_add_name(func_number, temp);
	  temp[0]=0;
	  temp_len=0;
	  start_args=1;
	  got_proto_name=1;
	} else {
	  printf("Not a proper proto definition -2: %s\n", proto);
	  return(0);
	}

      break;

      case ')':
        if (start_args) {
	  if (got_arg_type && temp_len) {
	    function_add_arg(func_number, temp2, temp);
	    temp[0]=0;
	    temp_len=0;
	  }
          done=1;
	} else {
	  printf("Not a proper proto definition -4: %s\n", proto);
	  return(0);
	}
      break;

      case ',':
        if (start_args && got_arg_type) {
	  got_arg_type=0;
          function_add_arg(func_number, temp2, temp);
	  temp2[0]=0;
	  temp[0]=0;
	  temp_len=0;
	} else {
	  printf("Not a proper proto definition -3: %s\n", proto);
	  return(0);
	}
      break;

      default:
        temp[temp_len]=c;
	temp_len++;
	temp[temp_len]=0;
      break;
    }
    if (done) {
      start=i+1;
      break;
    }
  }
  parse_desc(func_number, proto+start);
  return(1);
}



int parse_file(char *buffer)
{
  char *ptr=NULL, *temp1=NULL, *temp2=NULL;
  char *args=NULL;

  ptr=buffer;
  while (1) {
    temp1=strstr(ptr, "{{{");
    if (temp1 == NULL) break;
    temp2=strstr(temp1, "*/");
    if (temp2 == NULL) break;
    args=midstr(temp1, 3, strlen(temp1)-strlen(temp2)-3);
    parse_proto(args);
    free(args);
    ptr=temp2;
  }
  return(1);
}

int main(int argc, char **argv)
{
  char *contents=NULL;

  if (argc < 2) {
    printf("Usage: %s <extension file.c>\n", argv[0]);
    return(2);
  }
  contents=read_file(argv[1]);
  if (contents == NULL) {
    printf("Could not read %s\n", argv[1]);
  }
  parse_file(contents);
  free(contents);
  write_xml_files();
  return(1);
}