@echo off
setlocal
cd /d "%~dp0\.."

echo [SongChart] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1

echo [SongChart] Installing frontend dependencies...
if exist package-lock.json (
    call npm ci || exit /b 1
) else (
    call npm install || exit /b 1
)

echo [SongChart] Building Vite assets...
call npm run build || exit /b 1

if not exist public\build\manifest.json (
    echo [ERROR] public\build\manifest.json was not generated.
    exit /b 1
)

echo [SongChart] Running Phase 3 tests...
php artisan test --filter=UiPreviewTest || exit /b 1

echo [OK] SongChart v10.2 UI Preview applied successfully.
endlocal
