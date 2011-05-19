@ECHO OFF
SET PHP_DOC_BASE_DIR=%~dp0
svn update scripts/build-chms.php > nul
REM Make sure all working directories exist.
FOR %%D IN (tmp chmfiles chmfiles\logs) DO IF NOT EXIST "%PHP_DOC_BASE_DIR%\..\%%D" MD "%PHP_DOC_BASE_DIR%\..\%%D"
CALL "C:\php\binaries\PHP_5_3\php.exe" -d memory_limit=-1 "%PHP_DOC_BASE_DIR%\scripts\build-chms.php" %* > "%PHP_DOC_BASE_DIR%\..\chmfiles\logs\%~n0.log"
