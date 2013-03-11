@ECHO OFF

SET PHP_DOC_BASE_DIR=%~dp0

CALL svn update scripts/build-chms-config.php > nul
CALL svn update scripts/build-chms.php > nul
CALL "E:\software\php\php.exe" -d memory_limit=-1 "%PHP_DOC_BASE_DIR%\scripts\build-chms.php" %*
