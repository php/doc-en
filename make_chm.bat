@echo off

rem !! Please read the make_chm.README file for information
rem !! about how to build a manual_lang.chm file.

set PHP_PATH=D:\progra~1\php402\php.exe
set PHP_HELP_COMPILER=D:\progra~1\helpwo~1\hhc.exe
set PHP_HELP_COMPILE_LANG=hu
set PHP_HELP_COMPILE_DIR=html
set PHP_HELP_COMPILE_FANCYDIR=fancy

rem ==========================================================
rem !!!!!    DO NOT MODIFY ANYTHING BELOW THIS LINE      !!!!!
rem ==========================================================

echo.

if a%1a == anormala goto skipfancy

echo Now generating the fancy manual in %PHP_HELP_COMPILE_FANCYDIR% dir...
%PHP_PATH% -q make_chm_fancy.php

goto normal

:skipfancy
set PHP_HELP_COMPILE_FANCYDIR=
echo Skipping fancy manual generation...

:normal

echo Now running the toc and project file generator script...
%PHP_PATH% -q make_chm.php

echo Compiling the actual helpfile (manual_%PHP_HELP_COMPILE_LANG%.chm)...
%PHP_HELP_COMPILER% manual_%PHP_HELP_COMPILE_LANG%.chm

echo Done!
echo.
