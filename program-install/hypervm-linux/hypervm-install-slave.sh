#!/bin/sh
#
# HyperVM
# Install Script Version 1.0
# More information at http://www.lxcenter.org/
# Author dterweij
#
######
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
	echo Remove old installation package
	rm -f program-install.zip
	echo Downloading installation package from LxCenter
	wget http://download.lxcenter.org/download/program-install.zip
#
	echo Unpacking installation package	
	unzip -oq program-install.zip
	cd program-install/hypervm-linux
	echo Starting main installation script
	php lxins.php --install-type=slave $* | tee hypervm_install.log
}
#
# Check how we were called.
#
case "$1" in
  --virtualization-type=xen)
    start
    ;;
  --virtualization-type=openvz)
    start
    ;;
  --virtualization-type=NONE)
    start
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