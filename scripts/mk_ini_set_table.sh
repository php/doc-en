#! /bin/sh

# Quick hack to make a table of PHP options
# and where they can be changed
# Uses GNU grep and GNU awk
# Author: Jesus M. Castagnetto
# Created: Mon Mar 19 04:57:02 PST 2001
# Updated: Thu Apr 25 11:42:26 PDT 2002
# - look through all PHP_INI_ containing files
# - save table in the new split dir for the function
# Updated: Wed Nov 20 18:16:12 PST 2002
# - rearrange the order of the tables, put the PHP_INI_* definitions first.

cfiles=`grep -rl PHP_INI_ ../php4/*`
ini_set_table="en/reference/info/functions/ini_set_table";

awk 'BEGIN {
	print "<note>";
	print " <para>";
	print "  The PHP_INI_* constants used in the table below are defined as follows:";
	print "  <table>";
	print "   <thead>";
	print "    <row>";
	print "     <entry>Constant</entry>";
	print "     <entry>Value</entry>";
	print "     <entry>Meaning</entry>";
	print "    </row>";
	print "   </thead>";
	print "   <tbody>";
	print "    <row>";
	print "     <entry>PHP_INI_USER</entry>";
	print "     <entry>1</entry>";
	print "     <entry>Entry can be set in user scripts</entry>";
	print "    </row>";
	print "    <row>";
	print "     <entry>PHP_INI_PERDIR</entry>";
	print "     <entry>2</entry>";
	print "     <entry>Entry can be set in <filename>.htaccess</filename></entry>";
	print "    </row>";
	print "    <row>";
	print "     <entry>PHP_INI_SYSTEM</entry>";
	print "     <entry>4</entry>";
	print "     <entry>Entry can be set in <filename>php.ini</filename> or";
	print "      <filename>httpd.conf</filename></entry>";
	print "    </row>";
	print "    <row>";
	print "     <entry>PHP_INI_ALL</entry>";
	print "     <entry>7</entry>";
	print "     <entry>Entry can be set anywhere</entry>";
	print "    </row>";
	print "   </tbody>";
	print "  </table>";
	print " </para>";
	print "</note>";
	print "<table>\n <title>Configuration options</title>"
	print " <tgroup cols=\"3\">";
	print "  <thead>";
	print "   <row>";
	print "    <entry>Name</entry>";
	print "    <entry>Default</entry>";
	print "    <entry>Changeable</entry>";
	print "   </row>";
	print "  </thead>";
	print "  <tbody>";
}
$0 ~ /PHP_INI_.*\(/ && $0 !~ /^static/ && $0 !~ /PHP_INI_(BEGIN|END)/ {
nf = split($0,tmp,",");

varname = substr(tmp[1], index(tmp[1], "\""));
gsub("\"", "", varname);

vardef = tmp[2];
gsub("(\t| )+", "", vardef);
#if (index(vardef, "\""))
#	gsub("\"", "", vardef);

varmod = tmp[3];
gsub("(\t| )+", "", varmod);

print "    <row>";
print "     <entry>" varname "</entry>"
print "     <entry>" vardef "</entry>"
print "     <entry>" varmod "</entry>"
print "    </row>";
};
END {
	print "  </tbody>";
	print " </tgroup>";
	print "</table>";
	}' $cfiles > $ini_set_table

ls -l $ini_set_table
