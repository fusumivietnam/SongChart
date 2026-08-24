@echo off
call "%~dp0songchart.bat" dev down %*
exit /b %ERRORLEVEL%
