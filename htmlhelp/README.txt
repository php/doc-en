Build system of the new CHMs
============================

[See latest "official" output package online at
 http://php.net/docs-echm]

How to build a CHM manual with this system?

 0. Ensure that you have the latest phpdoc checkout,
    but that your XSL folder is dated 2002.12.31 23:00:00,
    since if you use XSL sheets from after this timestamp,
    the customizations will not work. Use the cvs date tag
    to get this version:

    From the phpdoc root directory:
        cvs update -dP -D'December 31, 2002 11:00pm' xsl

    {Volunteer to fix the customizations if you are willing
     to, but otherwise you cannot do much more but use the old
     sheets}

    Ensure however that the xsl/version.xml file is up to date,
    so you will build the latest function version information
    into the CHM.

 1. run "autoconf" in the phpdoc directory

 2. run "./configure --with-chm=yes"

    Optionally you may need to specify the
    "--with-xsltproc=path" option to explicitly
    provide the XSLTProc path.    

 3. Replace @DOCBOOKXSL_HTML@ with ./docbook/html/chunk.xsl in
    xsl/htmlhelp-db.xsl (do this after any configure runs). This
    is needed, since the new XSL sheets require no configuration,
    and since you are using old sheets, you need to do configuration
    yourself.

 4. Run "make chm_xsl"

    If xsltproc encounters errors in the XML files,
    correct the errors, commit them to phpdoc, and
    run "make chm_xsl" again. There is no need to
    reconfigure in most cases.
   
    After this step the HTML files to start are in
    phpdoc/htmlhelp/html

 5. Get the actual mirrors.inc file from
    http://ANY_MIRROR.php.net/include/mirrors.inc
    and save into the directory where the
    make_chm.bat resides (overwrite old one if
    one exists).

 6. Get all the user notes from
    http://ANY_MIRROR.php.net/backend/notes/all.bz2,
    extract its contents (using bunzip2 all.bz2, for example),
    and place the resulting "all" file to the same folder where
    the make_chm.bat resides.

 7. Copy local_vars.php.src to local_vars.php and
    adjust settings as needed.

 8. Now run make_chm.bat

Well, this is quite manual right now, and there are
some problems need fixing (see the TODO.txt file too).