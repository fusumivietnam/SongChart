@echo off
call "%~dp0songchart.bat" dev up %*
exit /b %ERRORLEVEL%
