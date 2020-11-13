@echo off
set param1=%1
echo Connecting to computer %param1:~8%...
C:\Windows\System32\msra.exe /offerra %param1:~8%
rem pause
