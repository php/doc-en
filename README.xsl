       THIS README FILE CONTAINS NOTES ON USING XSL STYLESHEETS
		     TO FORMAT PHP DOCUMENTATION

REQUIRED PACKAGES:
------------------

In order to successfully use XSL support you must have some XSLT
processor and XSL DocBook Stylesheets. This is sufficient for
generating HTML version of documentation. If you also want to create
version suitable for print, you will additionally need FO processor.

XSLT processors:
	XT: http://www.jclark.com/xml/xt.html
	Saxon: http://users.iclway.co.uk/mhkay/saxon/
	Xalan: http://xml.apache.org/xalan/

XSL DocBook Stylesheets:
	http://www.nwalsh.com/docbook/xsl/index.html

FO processors:
	PassiveTeX: http://users.ox.ac.uk/~rathz/passivetex/
	FOP: http://xml.apache.org/fop/
	XEP: http://www.renderx.com/


USAGE:
------

At this time there are no XSL specific targets in Makefile. This is
because there are not standardized ways to invoke XSLT processor. To
use XSL stylesheets you must run configure script and tell it, where
is placed your copy of XSL DocBook Stylesheets. Majority of XSLT
processor require URL instead of filename, so be sure to prepend
file:/// schema before path:

	autoconf
	./configure --with-xsl=file:///path/to/docbook/xsl/styles

Configure will create three files html.xsl, bightml.xsl and print.xsl
among others. These files can be used to generate desired
output. html.xsl generates set of small files, bightml.xsl generates
one large file and print.xsl generates file with FO object suitable
for further processing with some FO processor.

Invocation of XSLT processor is processor dependent, for now suppose
that you have shell script called saxon which is able to invoke Saxon
processor. Run:

	mkdir html
	saxon manual.xml html.xsl
		- to get HTML version of documentation

	saxon -o bigmanual.html manual.xml bightml.xsl
		- to get whole documentation in one large HTML file

	saxon -o manual.fo manual.xml print.xsl
		- to get FO file

For getting PDF from FO file you must run some FO processor on FO file.


PERFORMANCE COMMENTS:
---------------------

Generating bigmanual.html with Saxon (under JDK 1.3) takes about two
minutes on Celeron 400 computer. Generating FO files takes about two
times more + plus time required to run FO processor. Generating
chunked version of manual is on my machine approximately two times
slower than with Jade.


LIMITATIONS:
------------

Not all customization from DSSSL are backported to XSL, so some things
does not work for now. Hope, this will change in the near future.



Jirka Kosek <jirka@kosek.cz>
Last modified $Date: 2001-02-11 21:51:24 $
