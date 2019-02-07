#!/usr/bin/env bash

BASE=/opt/lampp
HTDOCS=$BASE/htdocs

sudo rm -f $HTDOCS/hl/install.php
sudo rm -rf $HTDOCS/hl/install_files

sudo mv $HTDOCS/hl $HTDOCS/install-master

sudo -u daemon git clone https://github.com/Fabian-Sommer/HeroesLounge $HTDOCS/hl/

sudo cp -r $HTDOCS/install-master/. $HTDOCS/hl/
sudo rm -rf $HTDOCS/install-master/

sudo unzip -qd ~/ /vagrant/hl.sql.zip
$BASE/bin/mysql -u root hl < ~/hl9.sql.sql

sudo chown daemon:daemon -R $HTDOCS

echo 'All done!'
