@echo off

rem !! Please read the make_chm.README file for information
rem !! about how to build a manual_lang.chm file.

echo.

set PHP_HELP_COMPILER=D:\progra~1\helpwo~1\hhc.exe
set PHP_HELP_COMPILE_LANG=hu
set PHP_HELP_COMPILE_DIR=html

if a%1a == anormala goto skipfancy

set PHP_HELP_COMPILE_FANCYDIR=fancy
echo Now making the fancy manual...
D:\progra~1\php402\php.exe -q make_chm_fancy.php

goto normal
:skipfancy
echo Skipping fancy manual generation...
:normal

echo Now running the toc and project file generator script...
D:\progra~1\php402\php.exe -q make_chm.php

echo Compiling the actual helpfile...
call compile.bat

echo Done!
echo.