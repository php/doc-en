@echo off
rem First untar the built html tree so that the "html" dir is a child of the
rem current directory.

rem Now run the toc generator:
echo "Running the toc and project file generator script:"
e:\php4\php make_chm.php > manual.hhc

rem After the toc is generated run the help compiler:
echo "Compiling the helpfile:"
e:\htmlhe~1\hhc manual.hhp
