@echo off
setlocal
cd /d %~dp0\..

php artisan optimize:clear || exit /b 1
php artisan test --filter=ControllerFoundationTest || exit /b 1
php artisan test --filter=AuthenticationAccountShellTest || exit /b 1

 echo Stage 09 Patch 01 applied successfully.
endlocal
