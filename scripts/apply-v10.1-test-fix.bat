@echo off
setlocal
cd /d %~dp0\..

echo Clearing Laravel caches...
php artisan optimize:clear
if errorlevel 1 exit /b 1

echo Running ULID regression test...
php artisan test --filter=UserUlidTest
if errorlevel 1 exit /b 1

echo Running shared shell tests...
php artisan test --filter=SharedShellsTest
if errorlevel 1 exit /b 1

echo [OK] v10.1 test database fix applied successfully.
endlocal
