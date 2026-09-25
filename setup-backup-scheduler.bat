@echo off
title Setup E-Library Windows Task Scheduler
cd /d " \%~dp0\\
echo ===============================================================
echo Register Windows Task Scheduler for E-Library Auto-Backup
echo ===============================================================
echo.
echo Creating Windows Task to run Laravel Scheduler every minute...
schtasks /create /tn \\ELibraryAutoBackup\\ /tr \\php \%~dp0artisan\ schedule:run\\ /sc minute /mo 1 /f
echo.
if %ERRORLEVEL% EQU 0 (
 echo [SUCCESS] Windows Task \\ELibraryAutoBackup\\ registered successfully!
) else (
 echo [NOTICE] Please run this script as Administrator if access was denied.
)
echo.
pause
