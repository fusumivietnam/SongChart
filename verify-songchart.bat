@echo off
call "%~dp0songchart.bat" verify %*
exit /b %ERRORLEVEL%
