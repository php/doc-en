Status of XSL Templates in this directory
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
The only used and supported files here as of DBK XSL 1.66.1 are:
HOWTO.XSL              - for generating HOW-TO with XSL
HTMLHELP.XSL           - for xCHM building process
HTMLHELP-CONFIG.XSL.IN - configuration parameters for the above

COMMON.XSL             - template with common options for HTMLHELP.XSL and
                         HOWTO.XSL (so far). Contains XSL code to tune
                         rendering of examples (programlisting) sections and 
                         general function reference customizations

VERSION.XML            - function to PHP version relation. Auto-generated from
                         the code to be found under the functable repository in
                         CVS or ask Hartmut =)  mailto:hartmut@php.net

QUICKREF.XSL           - for building functions quick reference 

All others are deprecated.

                         
HTML HELP (xCHM) NOTES
~~~~~~~~~~~~~~~~~~~~~~
HTML HELP (xCHM) generated from these templates is made of three customization
layers. Some parameters in XSL templates are not customizable and sometimes you
will need to copy whole big template to make a minor customization. Next time
DocBook templates upgrade you must merge these modifications. This becomes even
worse if there will be major structural changes in XSL DocBook (not likely, but
still possible) - that way you will have to start customizations almost from
scratch. Sometimes it is much more easier to patch output code with PHP scripts.

So, the first layer is "minor" customizations of DocBook XSL templates, where
.html and HTMLHELP project files are created without major templates redefining.
It is not that only XSL parameters are set and there are no templates redefined
at all. It is programmer's point of view on if templates should be redefined in
XSL or tuned via PHP scripts. The general rule probably is simplicity in
synchronizing customization layers with DocBook ones.

Second layer consists of PHP scripts in htmlhelp/ directory and mostly used for
quick patches/fixes, search_and_replace operations, that otherwise will require
big XSL overhaul. This layer also performs a very important role - it modifies
DOM structure of raw html files produced by DocBook XSL to add dynamic features, 
what are candy of xCHM. User notes, for example. 

Third layer is JavaScript code. It brings life to static xCHM pages, when user
decides to read them, manages skinning issues and all that stuff that is called
DHTML.


See htmlhelp/DESCRIPTION.txt with overview of requirements for output and input
html files from each layer.

===============================================================================
This document is written by techtonik (techtonik@php.net)
Contact him or the phpdoc list (phpdoc@lists.php.net) if you have any questions
or suggestions...

Last modified $Date$