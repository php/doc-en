#!/bin/sh
# 
# +----------------------------------------------------------------------+
# | PHP HTML Embedded Scripting Language Version 3.0                     |
# +----------------------------------------------------------------------+
# | Copyright (c) 1997,1998 PHP Development Team (See Credits file)      |
# +----------------------------------------------------------------------+
# | This program is free software; you can redistribute it and/or modify |
# | it under the terms of one of the following licenses:                 |
# |                                                                      |
# |  A) the GNU General Public License as published by the Free Software |
# |     Foundation; either version 2 of the License, or (at your option) |
# |     any later version.                                               |
# |                                                                      |
# |  B) the PHP License as published by the PHP Development Team and     |
# |     included in the distribution in the file: LICENSE                |
# |                                                                      |
# | This program is distributed in the hope that it will be useful,      |
# | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
# | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
# | GNU General Public License for more details.                         |
# |                                                                      |
# | You should have received a copy of both licenses referred to here.   |
# | If you did not, or have any questions about PHP licensing, please    |
# | contact core@php.net.                                                |
# +----------------------------------------------------------------------+
# | Authors:    Ariel Shkedi <ars@ziplink.net> or <as@altavista.net>     |
# |             Rasmus Lerdorf <rasmus@lerdorf.on.ca>                    |
# +----------------------------------------------------------------------+
# 
# $Id: genfunclist.sh,v 1.1 2002-01-06 15:42:31 hholzgra Exp $

for i in `find $1 -name "*.[c]" -print -o -name "*.ec" -print | xargs egrep -li function_entry` ; do
 echo $i | sed -e 's/\.\.\//# /'
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
