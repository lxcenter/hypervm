#!/bin/sh
#
# This file creates hypervm-install.zip for distribution from SVN.
# Please note to server admins, if install-hypervm-master/slave.sh is changed,
# update them on download server too.
#
####
echo "##########################################"
echo "### Creating hypervm-install.zip"
###
rm -f hypervm-install.zip
echo "### ......"
zip -rq hypervm-install.zip ../hypervm-install -x \
"../hypervm-install/CVS/*" \
"../hypervm-install/.svn/*" \
"../hypervm-install/hypervm-linux/CVS/*" \
"../hypervm-install/hypervm-linux/.svn/*"
####
echo "### hypervm-install.zip created."
echo "##########################################"
####
ls -l hypervm-install.zip
####
