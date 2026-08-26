@echo off
setlocal EnableExtensions
set "ROOT=%~dp0"
if /I "%~1"=="ai" if /I "%~2"=="status" (
  powershell -NoProfile -ExecutionPolicy Bypass -File "%ROOT%scripts\ai-status.ps1"
  exit /b %ERRORLEVEL%
)
powershell -NoProfile -ExecutionPolicy Bypass -File "%ROOT%scripts\songchart.ps1" %*
exit /b %ERRORLEVEL%
