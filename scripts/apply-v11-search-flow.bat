@echo off
setlocal
cd /d "%~dp0\.."
echo [1/4] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1
echo [2/4] Building frontend assets...
if exist package-lock.json (call npm ci) else (call npm install)
if errorlevel 1 exit /b 1
call npm run build || exit /b 1
echo [3/4] Running search flow tests...
php artisan test --filter=SearchFlowTest || exit /b 1
echo [4/4] Complete. Open http://songchart.test/search?q=Radiohead
endlocal
