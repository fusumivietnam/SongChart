@echo off
setlocal

if not exist vendor\autoload.php (
  echo [ERROR] Dependencies are not installed.
  echo Run: scripts\setup-laragon.bat
  exit /b 1
)

if not exist node_modules (
  echo [ERROR] Node dependencies are not installed.
  echo Run: scripts\setup-laragon.bat
  exit /b 1
)

echo Starting Vite development server...
start "SongChart Vite" cmd /k npm run dev

echo Starting queue worker...
start "SongChart Queue" cmd /k php artisan queue:work --tries=3

echo Laragon virtual host should serve the public directory.
echo Open: http://songchart.test
echo Design Lab: http://songchart.test/design-lab
endlocal
