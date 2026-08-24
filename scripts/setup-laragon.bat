@echo off
setlocal enabledelayedexpansion

echo ============================================
echo SongChartWeb Laragon Setup
echo ============================================

where php >nul 2>nul || (
  echo [ERROR] PHP not found in PATH.
  echo Open this script from Laragon Terminal.
  exit /b 1
)

where composer >nul 2>nul || (
  echo [ERROR] Composer not found in PATH.
  exit /b 1
)

where node >nul 2>nul || (
  echo [ERROR] Node.js not found in PATH.
  exit /b 1
)

where npm >nul 2>nul || (
  echo [ERROR] npm not found in PATH.
  exit /b 1
)

php -r "exit(version_compare(PHP_VERSION, '8.5.0', '>=') ? 0 : 1);" || (
  echo [ERROR] PHP 8.5 or newer is required.
  php -v
  exit /b 1
)

if not exist .env (
  copy .env.example .env >nul
  echo [OK] Created .env
)

echo [1/7] Installing PHP dependencies...
call composer install --no-interaction --prefer-dist
if errorlevel 1 exit /b 1

echo [2/7] Generating application key...
php artisan key:generate --force
if errorlevel 1 exit /b 1

echo [3/7] Installing JavaScript dependencies...
if exist package-lock.json (
  call npm ci
) else (
  call npm install
)
if errorlevel 1 exit /b 1

echo [4/7] Building frontend assets...
call npm run build
if errorlevel 1 exit /b 1

echo [5/7] Running database migrations...
php artisan migrate --seed
if errorlevel 1 (
  echo [WARN] Migration failed. Check PostgreSQL settings in .env.
  exit /b 1
)

echo [6/7] Linking public storage...
php artisan storage:link
if errorlevel 1 echo [WARN] storage:link may already exist.

echo [7/7] Running runtime checks...
call scripts\verify-runtime.bat
if errorlevel 1 exit /b 1

echo.
echo ============================================
echo Setup completed.
echo Open: http://songchart.test
echo Design Lab: http://songchart.test/design-lab
echo ============================================
endlocal
