@echo off

rem !! Please read the make_chm.README file for information
rem !! about how to build a "php_manual_lang.chm" file.

rem Path of the PHP CGI executable
set PHP_PATH=D:\progra~1\php404\php.exe

rem Path of the Help Compiler command line tool
set PHP_HELP_COMPILER=D:\progra~1\helpwo~1\hhc.exe

rem The language of the manual to compile
set PHP_HELP_COMPILE_LANG=en

rem The source directory with the original DSSSL made HTML
set PHP_HELP_COMPILE_DIR=html

rem The directory, where the fancy files need to be copied
set PHP_HELP_COMPILE_FANCYDIR=fancy

rem ==========================================================
rem !!!!!    DO NOT MODIFY ANYTHING BELOW THIS LINE      !!!!!
rem ==========================================================

echo.

if a%1a == anormala goto skipfancy

echo Now generating the fancy manual in %PHP_HELP_COMPILE_FANCYDIR% dir...
IF NOT EXIST %PHP_HELP_COMPILE_FANCYDIR%\NUL md %PHP_HELP_COMPILE_FANCYDIR%
IF NOT EXIST %PHP_HELP_COMPILE_FANCYDIR%\figures md %PHP_HELP_COMPILE_FANCYDIR%\figures
copy %PHP_HELP_COMPILE_DIR%\figures\*.* %PHP_HELP_COMPILE_FANCYDIR%\figures\*.*
%PHP_PATH% -q make_chm_fancy.php

goto normal

:skipfancy
set PHP_HELP_COMPILE_FANCYDIR=
echo Skipping fancy manual generation...

:normal

echo Now running the toc and project file generator script...
%PHP_PATH% -q make_chm.php

echo Compiling the actual helpfile (php_manual_%PHP_HELP_COMPILE_LANG%.chm)...
%PHP_HELP_COMPILER% php_manual_%PHP_HELP_COMPILE_LANG%.hhp

echo.
echo Cleaning up the directory
rem del php_manual_%PHP_HELP_COMPILE_LANG%.hh?

echo Done!
echo.
