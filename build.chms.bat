@ECHO OFF
REM This file assumes phpdoc/modules/doc-all checkout

SET PHP=c:\php53\php.exe
SET PHD=C:\pear\phd.bat
SET HH=C:\Program Files (x86)\HTML Help Workshop\hhc.exe
SET CP=C:\Program Files (x86)\PuTTY\pscp.exe
SET PRIVATES=C:\Documents and Settings\bjori\My Documents\my.privates.ppk
SET PEAR=C:\pear\pear.bat
SET SVN=C:\Program Files\SlikSvn\bin\svn.exe

cd c:\doc-all\

ECHO SVN Updating...
%SVN% up > logs\cvs.log 2<&1
ECHO Upgrading PhD
CALL "%PEAR%" upgrade doc.php.net/phd-beta > logs\pear.log 2<&1

FOR %%A IN (en bg de es fr ja kr pl pt_BR ro ru tr) DO (
	ECHO Configuring %%A...
	"%PHP%" doc-base/configure.php --with-php=%PHP% --with-lang=%%A --enable-chm > logs\configure.%%A.log 2<&1

	IF EXIST doc-base/.manual.xml (
		ECHO Running PhD...
		CALL "%PHD%" -d doc-base/.manual.xml -f xhtml -t chmsource -o tmp --lang %%A > logs\phd.%%A.log 2<&1

		ECHO Generating the chm...
		"%HH%" tmp/chm/php_manual_%%A.hhp > logs\hhc.%%A.log 2<&1

		"%CP%" -batch -q -i "%PRIVATES%"  -l bjori c:\doc-all\tmp\chm\php_manual_%%A.chm rsync.php.net:/home/bjori/manual-chms-new/

		move tmp\chm\php_manual_%%A.chm chmfiles/
	) ELSE (
		echo Build error!
	)

	ECHO Cleaning temp files...
	del /F /Q C:\doc-all\tmp
	del /Q C:\doc-all\doc-base\.manual.xml
	rmdir /s /q C:\doc-all\tmp\chm\res
	rmdir /s /q C:\doc-all\tmp\chm\
	rmdir /s /q C:\doc-all\tmp\html
	rmdir /s /q C:\doc-all\tmp
)

echo Done!
