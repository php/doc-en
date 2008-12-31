@ECHO OFF
SET PHP=c:\php53\php.exe
SET PHD=C:\pear\phd.bat
SET HH=C:\Program Files (x86)\HTML Help Workshop\hhc.exe
SET CP=C:\Program Files (x86)\PuTTY\pscp.exe
SET PRIVATES=C:\Documents and Settings\bjori\My Documents\my.privates.ppk
cd c:\phpdoc\

ECHO CVS Updating...
"C:\Program Files (x86)\CVSNT\cvs" up > logs\cvs.log 2<&1

FOR %%A IN (en bg de es fr ja kr pl pt_BR ro ru tr) DO (
	ECHO Configuring %%A...
	"%PHP%" configure.php --with-php=%PHP% --with-lang=%%A --enable-chm > logs\configure.%%A.log 2<&1

	IF EXIST .manual.xml (
		ECHO Running PhD...
		CALL "%PHD%" -d .manual.xml -f xhtml -t chmsource -o tmp --lang %%A > logs\phd.%%A.log 2<&1

		ECHO Generating the chm...
		"%HH%" tmp/chm/php_manual_%%A.hhp > logs\hhc.%%A.log 2<&1

		"%CP%" -batch -q -i "%PRIVATES%"  -l bjori tmp\chm\php_manual_%%A.chm rsync.php.net:/home/bjori/manual-chms-new/

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
