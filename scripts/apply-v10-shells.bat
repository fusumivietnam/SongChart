@echo off
setlocal
cd /d "%~dp0\.."
echo Clearing Laravel caches...
php artisan optimize:clear
if errorlevel 1 exit /b 1

echo Building Vite assets...
if exist package-lock.json (call npm ci) else (call npm install)
if errorlevel 1 exit /b 1
call npm run build
if errorlevel 1 exit /b 1

echo Running shell tests...
php artisan test --filter=SharedShellsTest
if errorlevel 1 exit /b 1

echo [OK] Phase 2 shared shells are ready.
endlocal
