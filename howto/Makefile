#######################################
# phpdoc-HOWTO Makefile
#
# Daniel Beckham <danbeck@php.net>
#
#######################################
SOURCEDIR=.
LDPDIR=.
JADE=jade


all:  html

html:
	@test -d html || mkdir html
	$(JADE) -i lang-en -d $(SOURCEDIR)/phpdoc-howto.dsl -V use-output-dir -t sgml ../$(SOURCEDIR)/phpdocxml.dcl $(SOURCEDIR)/phpdoc-howto.xml

clean:
	rm -rf ./html
