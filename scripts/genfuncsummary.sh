#!/bin/sh
# $Id: genfuncsummary.sh,v 1.2 2002-01-07 10:25:09 goba Exp $

if test -f funcsummary.awk; then
  awkscript=funcsummary.awk
elif test -f scripts/funcsummary.awk; then
  awkscript=scripts/funcsummary.awk
else
  echo 1>&2 funcsummary.awk not found
	exit
fi
	

for i in `find $1 -name "*.[ch]" -print -o -name "*.ec" -print | xargs egrep -li "{{{ proto"` ; do
 echo $i | sed -e 's/\.\.\//# /'
 awk -f $awkscript < $i | sort +1 | awk -F "---" '{ print $1; print $2; }' | sed 's/^[[:space:]]+//'
done
if test -f $1/language-scanner.lex # only in PHP3
then
  awk -f funcsummary.awk < $1/language-scanner.lex | sort +1 | awk -F "---" '{ print $1; print $2; }'
fi
