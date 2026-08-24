@echo off
setlocal EnableExtensions
set "ROOT=%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%ROOT%scripts\songchart.ps1" %*
exit /b %ERRORLEVEL%
