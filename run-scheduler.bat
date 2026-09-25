@echo off
title E-Library Automation Worker (php artisan schedule:work)
cd /d " \%~dp0\\
echo ===============================================================
echo E-Library Automated Schedule Worker (Drive D Auto-Backup)
echo ===============================================================
echo Keeping scheduler running in background...
echo (Press Ctrl+C to stop)
echo.
php artisan schedule:work
pause
