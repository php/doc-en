****************************************************************
** This build system is used to generate the extended CHM     **
** file available from php.net (only in English). There is    **
** a different CHM generator system in the 'chm' folder,      **
** which is used to build the simpler CHM files (in multiple  **
** languages).                                                **
**                                                            **
** Both of the systems are used in paralell.                  **
****************************************************************

Build system of the extended CHMs
=================================

[See latest "official" output package online at
 http://php.net/docs-echm]

How to build a CHM manual with this system?

 0. Ensure that you have the latest phpdoc checkout and the
    version information in xsl/version.xml is up-to-date,
    so you will build the latest function version information
    into the CHM.

 1. run "autoconf" in the phpdoc directory

 2. run "./configure --with-chm=yes"

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

 5. Get all the user notes from
    http://ANY_MIRROR.php.net/backend/notes/all.bz2,
    extract its contents (using bunzip2 all.bz2, for example),
    and place the resulting "all" file to the same folder where
    the make_chm.bat resides.

 6. Copy local_vars.php.src to local_vars.php and
    adjust settings as needed.

 7. Now run make_chm.bat

Well, this is quite manual right now, and there are
some problems need fixing (see the TODO.txt file too).
