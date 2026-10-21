@echo off
title TRACE — Lost & Found Intelligence Network
echo ========================================================
echo  TRACE — Lost & Found Intelligence Network
echo ========================================================
echo  Starting local development server...
echo  URL: http://localhost:8000
echo  Press Ctrl+C to stop the server.
echo ========================================================
echo.

if exist "C:\xampp\php\php.exe" (
    "C:\xampp\php\php.exe" -S localhost:8000 -t public
) else (
    php -S localhost:8000 -t public
)
