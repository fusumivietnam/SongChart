@echo off
setlocal
cd /d "%~dp0\.."

echo [SongChart] Applying Stage 04 Patch 01...
php artisan optimize:clear || exit /b 1
php artisan test --filter=SearchFlowTest || exit /b 1

echo.
echo [SongChart] Stage 04 Patch 01 applied successfully.
endlocal
