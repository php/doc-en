#!/bin/sh
# 
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
# | Authors:    Ariel Shkedi <ars@ziplink.net> or <as@altavista.net>     |
# |             Rasmus Lerdorf <rasmus@lerdorf.on.ca>                    |
# +----------------------------------------------------------------------+
# 
# $Id: genfunclist.sh,v 1.4 2002-01-27 17:33:25 hholzgra Exp $

for i in `find $1 -name "*.[c]" -print -o -name "*.ec" -print | xargs egrep -li function_entry | sort` ; do
 echo $i | sed -e "s|$1|# php4|"
 if test -f funcparse.awk ; then
  awk -f funcparse.awk < $i | sort
 elif test -f scripts/funcparse.awk; then
  awk -f scripts/funcparse.awk < $i | sort
 else 
  echo 1>&2 funcparse.awk not found
  exit
 fi
done
if test -f $1/language-scanner.lex # only in PHP3 sources
then 
 echo $1/language-scanner.lex | sed -e 's/\.\.\//# /'
 awk -f funcparse.awk < $1/language-scanner.lex | sort
fi
