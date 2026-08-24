@echo off
setlocal
cd /d "%~dp0\.."
echo [SongChart] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1
if exist package-lock.json (call npm ci) else (call npm install)
if errorlevel 1 exit /b 1
call npm run build || exit /b 1
php artisan test --filter=EntityDetailSystemTest || exit /b 1
php artisan test --filter=SearchFlowTest || exit /b 1
echo [SongChart] Stage 07 applied successfully.
endlocal
