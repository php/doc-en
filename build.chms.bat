@ECHO OFF
SET PHP=c:\php53\php.exe
SET PHD=C:\pear\phd.bat
SET HH=C:\Program Files (x86)\HTML Help Workshop\hhc.exe

cd c:\phpdoc\

ECHO CVS Checkout...
"C:\Program Files (x86)\CVSNT\cvs" up > cvs.log 2<&1

ECHO Configuring...
"%PHP%" configure.php --with-php=%PHP% > configure.log 2<&1

ECHO Running PHD...
CALL "%PHD%" -d .manual.xml -f xhtml -t chmsource -o tmp > phd.log 2<&1

ECHO Generating the chm...
"%HH%" tmp/chm/php_manual_en.hhp   > hhc.log 2<&1
move tmp\chm\php_manual_en.chm chmfiles/

ECHO Cleaning temp files...
del /F /Q C:\phpdoc\tmp
rmdir /s /q C:\phpdoc\tmp\chm\res
rmdir /s /q C:\phpdoc\tmp\chm\
rmdir /s /q C:\phpdoc\tmp\html
rmdir /s /q C:\phpdoc\tmp

echo Done!
