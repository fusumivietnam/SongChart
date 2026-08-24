@echo off
setlocal
cd /d %~dp0\..
php artisan optimize:clear || exit /b 1
if exist package-lock.json (call npm ci) else (call npm install)
if errorlevel 1 exit /b 1
call npm run build || exit /b 1
php artisan test --filter=ProviderChooserTest || exit /b 1
php artisan test --filter=EntityDetailSystemTest || exit /b 1
echo Stage 08 provider chooser applied successfully.
