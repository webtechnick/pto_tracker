#!/bin/sh

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.

sudo npm install -g n
sudo n 10.17.0

git config --global user.name = "Nick Baker"
git config --global user.email = "nick@webtechnick.com"
git config --global push.default simple
. /home/vagrant/code/aliases