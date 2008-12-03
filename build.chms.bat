@ECHO OFF
SET PHP=c:\php53\php.exe
SET PHD=C:\pear\phd.bat
SET HH=C:\Program Files (x86)\HTML Help Workshop\hhc.exe

cd c:\phpdoc\

ECHO CVS Updating...
REM "C:\Program Files (x86)\CVSNT\cvs" up > logs\cvs.log 2<&1

FOR %%A IN (en ja fr) DO (
	ECHO Configuring %%A...
	"%PHP%" configure.php --with-php=%PHP% --with-lang=%%A > logs\configure.%%A.log 2<&1

	IF EXIST .manual.xml (
		ECHO Running PhD...
		CALL "%PHD%" -d .manual.xml -f xhtml -t chmsource -o tmp --lang %%A > logs\phd.%%A.log 2<&1

		ECHO Generating the chm...
		"%HH%" tmp/chm/php_manual_%%A.hhp > logs\hhc.%%A.log 2<&1
		move tmp\chm\php_manual_%%A.chm chmfiles/
	) ELSE (
		echo Build error!
	)

	ECHO Cleaning temp files...
	del /F /Q C:\phpdoc\tmp
	del /Q C:\phpdoc\.manual.xml
	rmdir /s /q C:\phpdoc\tmp\chm\res
	rmdir /s /q C:\phpdoc\tmp\chm\
	rmdir /s /q C:\phpdoc\tmp\html
	rmdir /s /q C:\phpdoc\tmp
)

echo Done!
