#!/usr/bin/env bash

BASE=/opt/lampp
HTDOCS=$BASE/htdocs

if [ ! -f /vagrant/hl.sql.zip ]
then
  echo "db zip must be present in /vagrant to run this!"
  exit
fi

echo 'Deleting, moving, symlinking'
sudo rm -f $HTDOCS/hl/install.php
sudo rm -rf $HTDOCS/hl/install_files

sudo mv $HTDOCS/hl $HTDOCS/install-master
sudo -u daemon ln -s /vagrant $HTDOCS/hl

echo 'Copying (takes a long while)'
sudo cp /vagrant/README.md $HTDOCS/install-master/
sudo cp /vagrant/.gitignore $HTDOCS/install-master/

sudo -u vagrant cp -r $HTDOCS/install-master/. $HTDOCS/hl/
sudo rm -rf $HTDOCS/install-master/

echo 'Importing db (takes a short while)'
sudo unzip -qd ~/ /vagrant/hl.sql.zip
$BASE/bin/mysql -u root hl < ~/hl9.sql.sql

echo 'All done!'
