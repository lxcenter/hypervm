#!/bin/sh
#
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009     LxLabs
#    Copyright (C) 2009          LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
# HyperVM
# Install Script Version 1.0.1
# More information at http://www.lxcenter.org/
# Author dterweij
#
######
clear
cat LICENSE
echo "--------------------------------------------"

start() {

	export PATH=/usr/sbin:/sbin:$PATH

if ! [ -f /usr/bin/yum ] ; then
      	echo You at least need yum installed for this to work...
	echo Please contact us or visit the forum at http://forum.lxcenter.org
	echo "                                "
	exit
fi
#
if [ -f /usr/bin/yum ] ; then
	echo Installing some packages with yum
	yum -y install php wget zip unzip 
else 
	echo Installing some packages with up2date
	up2date --nox --nosig php wget zip unzip
fi
#
	echo Checking if php is installed
if ! [ -f /usr/bin/php ] ; then
	echo Installing php failed. Please fix yum/up2date.
	exit
fi
#
if 	[ -f ./hypervm-install.zip ] ; then
	echo Remove old installation package
	rm -f hypervm-install.zip
fi

if [ ! -d '../../.git' ]; then
	pwd
	echo Downloading installation package from LxCenter
	wget http://download.lxcenter.org/download/hypervm-install.zip
else
	echo 'Development GIT version found. Skipping download sources.'
fi
#
	
	if [ ! -d '../../.git' ]; then
		echo "Unpacking installation package"	
		unzip -oq hypervm-install.zip
	else
		echo "Unpacking installation package from current development version"
		unzip -oq ../hypervm-install.zip
	fi
	cd hypervm-install/hypervm-linux
	echo Starting main installation script
	php lxins.php --install-type=master $1 $2 | tee hypervm_install.log
}
#
# Check how we were called.
#
case "$1" in
  --virtualization-type=xen)
	echo 'Installing HyperVM with Xen virtualization'
    start $*
    ;;
  --virtualization-type=openvz)
	echo 'Installing HyperVM with OpenVZ virtualization'
    start $*
    ;;
  --virtualization-type=NONE)
	echo 'Installing HyperVM with default(Xen) virtualization'
    start $*
    ;;
  *)
   	echo $"This is the HyperVM Install script"
    	echo $"The usage is:"
    	echo $"sh $0 --virtualization-type=xen/openvz/NONE [--skipostemplate=true]"
	exit 1
esac
exit $?
#
# End
