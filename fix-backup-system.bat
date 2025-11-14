@echo off
echo Running Database Backup System Fix...
echo.

REM Get the PHP executable path from XAMPP
set PHP_EXE=C:\xampp\php\php.exe

REM Check if PHP exists at the default location
if not exist "%PHP_EXE%" (
    echo PHP not found at %PHP_EXE%
    echo Please enter the full path to your PHP executable:
    set /p PHP_EXE=
)

REM Run the fix script
"%PHP_EXE%" fix-backup-system.php

echo.
echo Fix completed. Press any key to exit.
pause > nul
