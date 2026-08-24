@echo off
setlocal

cd /d "%~dp0\.."

echo ============================================
echo SongChart Vite Asset Build
 echo ============================================

where node >nul 2>nul || (
  echo [ERROR] Node.js is not available in PATH.
  echo Install Node.js LTS or enable it in Laragon, then reopen Laragon Terminal.
  exit /b 1
)

where npm >nul 2>nul || (
  echo [ERROR] npm is not available in PATH.
  exit /b 1
)

echo Node:
node --version
npm --version

if exist package-lock.json (
  echo [1/4] Installing dependencies with npm ci...
  call npm ci
) else (
  echo [1/4] package-lock.json is missing; installing with npm install...
  call npm install
)
if errorlevel 1 exit /b 1

echo [2/4] Building Vite assets...
call npm run build
if errorlevel 1 exit /b 1

if not exist public\build\manifest.json (
  echo [ERROR] Build finished but public\build\manifest.json was not created.
  exit /b 1
)

echo [3/4] Clearing Laravel view/config caches...
if exist vendor\autoload.php (
  php artisan optimize:clear
) else (
  echo [WARN] vendor\autoload.php is missing; Composer dependencies are not installed.
)

echo [4/4] Verifying assets...
for %%F in (public\build\manifest.json) do echo [OK] %%~fF ^(%%~zF bytes^)

echo.
echo Assets are ready. Reload http://songchart.test
endlocal
