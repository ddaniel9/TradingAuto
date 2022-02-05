@echo off
echo %time%
php ../buycoin.php
timeout 900 
call UpBitCoin.bat
