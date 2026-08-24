@echo off
setlocal
cd /d "%~dp0\.."

echo [SongChart] Applying Stage 06 Search Results...
php artisan optimize:clear || exit /b 1

if exist package-lock.json (
    call npm ci || exit /b 1
) else (
    call npm install || exit /b 1
)

call npm run build || exit /b 1
if not exist public\build\manifest.json (
    echo [ERROR] Vite manifest was not generated.
    exit /b 1
)

php artisan test --filter=SearchResultsTest || exit /b 1
php artisan test --filter=SearchFlowTest || exit /b 1
php artisan test --filter=HomepageTest || exit /b 1

echo [SongChart] Stage 06 applied successfully.
endlocal
