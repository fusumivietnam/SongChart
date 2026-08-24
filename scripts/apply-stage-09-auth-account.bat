@echo off
setlocal
cd /d "%~dp0\.."

echo [1/5] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1

echo [2/5] Installing frontend dependencies...
if exist package-lock.json (call npm ci) else (call npm install)
if errorlevel 1 exit /b 1

echo [3/5] Building Vite assets...
call npm run build || exit /b 1

echo [4/5] Running authentication/account tests...
php artisan test --filter=AuthenticationAccountShellTest || exit /b 1

echo [5/5] Running existing user/admin regressions...
php artisan test --filter=UserUlidTest || exit /b 1
php artisan test --filter=SharedShellsTest || exit /b 1

echo Stage 09 applied successfully.
endlocal
