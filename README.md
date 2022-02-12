# TradingAuto




#AVVIO AUTOMATICO LINUX:

cd /etc/systemd/system
touch bitcoin.service
sudo nano bitcoin.service


    [Unit]
    Description=Avvio Automatico Bot Bitcoin
    After=network.target
    [Service]
    Type=simple
    WorkingDirectory=/root
    ExecStart=/bin/sh /root/coincron.sh
    Restart=always
    [Install]
    WantedBy=multi-user.target



systemctl enable bitcoin.service

reboot.


#COMMAND:
systemctl stop bitcoin.service
ps aux | grep coincron.sh
kill processsss



#PHP
#php.ini:
       Windows:     max_execution_time=1000
              abilitare sotto la voce [curl] :
              curl.cainfo ="C:\Program Files\PHP\cert\cacert.pem"
              abilitare sotto la voce [openssl] :
              openssl.cafile="C:\Program Files\PHP\cert\cacert.pem"
            Seguire Guida: https://stackoverflow.com/questions/28858351/php-ssl-certificate-error-unable-to-get-local-issuer-certificate
            Cert: https://curl.se/docs/caextract.html

            extension:
             scrivere: 
              [PECL]
              extension=trader
             scaricare da: 
              trader :
                https://stackify.dev/564966-install-php-trader-dll-in-wamp-server-3-0-6-x64-on-windows
                https://pecl.php.net/package/trader/0.5.1/windows
			
estensione trader_php per Linux :
					sudo apt-get install -y php-pecl-http
					sudo apt-get install php-pear php7-dev
	apt-get install php-pear php-dev libcurl3-openssl-dev
	pecl install trader
	scrivere sul file php.ini : extension=trader.so :
				cercare il file: php -i | grep ini
				scrivere: nano  /etc/php/7.4/cli/php.ini
riavviare PHP:
		se nginx: service nginx restart



bot telegram:
https://www.youtube.com/watch?v=hE0lH-5BATI
