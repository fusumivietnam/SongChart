@echo off
setlocal
cd /d "%~dp0.."

echo [1/4] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1

echo [2/4] Installing frontend dependencies...
if exist package-lock.json (
  call npm ci || exit /b 1
) else (
  call npm install || exit /b 1
)

echo [3/4] Building Vite assets...
call npm run build || exit /b 1
if not exist public\build\manifest.json (
  echo ERROR: public\build\manifest.json was not created.
  exit /b 1
)

echo [4/4] Running Stage 05 tests...
php artisan test --filter=HomepageTest || exit /b 1
php artisan test --filter=SearchFlowTest || exit /b 1

echo Stage 05 homepage applied successfully.
endlocal
