@echo off

rem First build the html tree or untar the built html tree,
rem so that the "html" dir is a child of the current directory.

rem Set the needed arguments here
set PHP_HELP_COMPILER=D:\progra~1\helpwo~1\hhc.exe
set PHP_HELP_COMPILE_LANG=hu
set PHP_HELP_COMPILE_DIR=html

echo.
echo Now running the toc and project file generator script...
D:\progra~1\php402\php.exe -q make_chm.php

echo Compiling the actual helpfile...
call compile.bat

echo Done!
echo.