@echo off
setlocal
cd /d "%~dp0\.."

echo [1/3] Clearing Laravel caches...
php artisan optimize:clear || exit /b 1

echo [2/3] Running Search Results regression tests...
php artisan test --filter=SearchResultsTest || exit /b 1

echo [3/3] Running related search tests...
php artisan test --filter=SearchFlowTest || exit /b 1

echo Stage 06 Patch 01 applied successfully.
endlocal
