Build system of the new CHMs
============================

[See latest "official" output package online at
 http://weblabor.hu/php-doc-chm]

How to build a CHM manual with this system?

 1. run "autoconf" in the phpdoc directory

 2. run "./configure --with-chm=yes --with-xsl=yes"

    Optionally you may need to specify the
    "--with-xsltproc=path" option to explicitly
    provide the XSLTProc path.    

 3. Run "make chm_xsl"

    If xsltproc encounters errors in the XML files,
    correct the errors, commit them to phpdoc, and
    run "make chm_xsl" again. There is no need to
    reconfigure in most cases.
   
    After this step the HTML files to start are in
    phpdoc/htmlhelp/html

 4. Get the actual mirrors.inc file from
    http://ANY_MIRROR.php.net/include/mirrors.inc
    and save into the directory where the
    make_chm.bat resides (overwrite old one if
    one exists).

 6. Get all the user notes from
    http://ANY_MIRROR.php.net/backend/notes/ALL.bz2,
    extract it's contents, and place the resulting
    "all" file to the same folder where the
    make_chm.bat resides.

 7. Copy local_vars.php.src to local_vars.php and
    adjust settings as needed.

 8. Now run make_chm.bat

Well, this is quite manual right now, and there are
some problems need fixing (see the TODO.txt file too).