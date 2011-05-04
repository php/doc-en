@ECHO OFF
SET PHP_DOC_BASE_DIR=%~dp0
svn update scripts/build-chms.php > nul
IF NOT EXIST "%PHP_DOC_BASE_DIR%\..\logs" MD "%PHP_DOC_BASE_DIR%\..\logs"
CALL "C:\php\binaries\PHP_5_3\php.exe" -d memory_limit=-1 "%PHP_DOC_BASE_DIR%\scripts\build-chms.php" %* > "%PHP_DOC_BASE_DIR%\..\logs\%~n0.log"
