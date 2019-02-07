#!/usr/bin/env bash

BASE=/opt/lampp
HTDOCS=$BASE/htdocs

wget https://www.apachefriends.org/xampp-files/7.2.14/xampp-linux-x64-7.2.14-0-installer.run
chmod +x xampp-linux-*-installer.run
sudo ./xampp-linux-*-installer.run
rm ./xampp-linux-*-installer.run

sudo apt-get install unzip -y

#curl -s https://octobercms.com/api/installer | php
wget -O ~/october-install.zip https://octobercms.com/download
sudo unzip -d $HTDOCS/ ~/october-install.zip
sudo mv $HTDOCS/install-master $HTDOCS/hl

sudo chown daemon:daemon -R $HTDOCS

sudo $BASE/lampp restart
echo -e '\nsudo /opt/lampp/lampp restart' >> ~/.profile

sleep 5
$BASE/bin/mysql -u root -e "CREATE DATABASE hl CHARACTER SET utf8 COLLATE utf8_general_ci;"

echo 'Now go back to the README for the next steps'
