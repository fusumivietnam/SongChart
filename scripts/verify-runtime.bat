@echo off
setlocal

echo Checking Laravel runtime...

if not exist vendor\autoload.php (
  echo [ERROR] vendor\autoload.php is missing.
  exit /b 1
)

if not exist node_modules (
  echo [ERROR] node_modules is missing.
  exit /b 1
)

if not exist public\build\manifest.json (
  echo [ERROR] Vite build manifest is missing.
  echo Run: npm run build
  exit /b 1
)

php artisan about
if errorlevel 1 exit /b 1

php artisan route:list --path=design-lab
if errorlevel 1 exit /b 1

php artisan test --filter=DesignLabTest
if errorlevel 1 exit /b 1

echo [OK] Laravel, Vite and Design Lab are ready.
endlocal
