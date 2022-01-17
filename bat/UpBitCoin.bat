@echo off
echo %time%
php ../buycoin.php
timeout 300 
call UpBitCoin.bat
