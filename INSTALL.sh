#!/bin/bash

function load()
{
    text=$1
    oft=$2
    i=0
    while [ "$i" -lt "$oft" ]; do
    echo -en "\015$text \033[31m[|]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[/]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[-]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[\]\033[0m"
    sleep 0.1
    i=$(( $i + 1 ))
    done
    sleep 0.1
    echo -en "\015$text \033[32m[done]\033[0m"
    echo ""
}

load "Starting Installation Tool" 3

sudo rm -rf /srv/www/htdocs/*
bash GET_EMOJIS.sh
sudo cp -r * /srv/www/htdocs/
sudo zypper update -y

load "Installing PHP" 5
sudo zypper install -y apache2
sudo systemctl start apache2
sudo systemctl enable apache2

load "Installing Firewall" 5
sudo zypper install -y SuSEfirewall2
sudo cat /etc/sysconfig/SuSEfirewall2 | grep 'FW_SERVICES_EXT_TCP=""'
sudo sed -i 's/^FW_SERVICES_EXT_TCP="".*/FW_SERVICES_EXT_TCP="80"/' /etc/sysconfig/SuSEfirewall2
sudo systemctl restart SuSEfirewall2
sudo chown wwwrun /srv/www/htdocs/ -R

load "Installing MariaDB" 5
sudo zypper install -y mariadb mariadb-client mariadb-tools
sudo systemctl start mysql
sudo systemctl enable mysql
sudo mysql -u root --password='' < install.sql
sudo mysql -u MAINPAGE --password='MAINPAGE' MAINPAGE < database_mainpage.sql

load "Installing PHP" 5
sudo zypper install -y php7 php7-mysql apache2-mod_php7
sudo a2enmod php7
sudo systemctl restart apache2

load "Installing MAINPAGE" 5
load "Start MAINPAGE" 2
