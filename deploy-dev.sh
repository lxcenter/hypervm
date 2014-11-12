#!/bin/sh
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2014	LxCenter
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
#	 author: Ángel Guzmán Maeso <angel.guzman@lxcenter.org>
#
#    Install and deploy a develoment version on a local enviroment
#    
#    Version 0.5 Patched by dkstiler with semir changes (better args handling) for HyperVM [ Dionysis Kladis <dkstiler@gmail.com> ] 
#    Version 0.4 Added which, zip and unzip as requirement [ Danny Terweij <d.terweij@lxcenter.org> ]
#    Version 0.3 Added perl-ExtUtils-MakeMaker as requirement to install_GIT [ Danny Terweij <d.terweij@lxcenter.org> ]
#    Version 0.2 Changed git version [ Danny Terweij <d.terweij@lxcenter.org> ]
#    Version 0.1 Initial release [ Ángel Guzmán Maeso <angel.guzman@lxcenter.org> ]
#
HYPERVM_PATH='/usr/local/lxlabs'

REPO="lxcenter"
BRANCH="dev"

usage(){
echo "Usage: $0 [BRANCH] [REPOSITORY] [-h]"
echo "-b : BRANCH (optional): git branch (like: $BRANCH)"
echo "-r : REPOSITORY (optional): the repo you want to use (like: $REPO)"
echo 'h: shows this help.'
exit 1
}

while getopts “h:r:b:” OPTION
do
case $OPTION in
h)
usage
exit 1
;;
r)
REPO="$OPTARG"
;;
b)
BRANCH="$OPTARG"
;;
?)
usage
exit
;;
esac
done
echo "Using REPO: $REPO BRANCH: $BRANCH "


install_GIT()
{
	# Redhat based
	if [ -f /etc/redhat-release ] ; then
		# Install git with curl and expat support to enable support on github cloning
		yum install -y gcc gettext-devel expat-devel curl-devel zlib-devel openssl-devel perl-ExtUtils-MakeMaker
	# Debian based
	elif [ -f /etc/debian_version ] ; then
		# No tested
		apt-get install gcc
	fi
	
	# @todo Try to get the lastest version from some site. LATEST file?
	GIT_VERSION='1.9.0'
	
	echo "Downloading and compiling GIT ${GIT_VERSION}"
	wget http://git-core.googlecode.com/files/git-${GIT_VERSION}.tar.gz
	tar xvfz git-*.tar.gz; cd git-*;
	./configure --prefix=/usr --with-curl --with-expat
	make all
	make install
	
	echo 'Cleaning GIT files.'
	cd ..; rm -rf git-*
}

require_root()
{
	if [ `/usr/bin/id -u` -ne 0 ]; then
    	echo 'Please, run this script as root.'
    	usage
	fi
}

require_requirements()
{
    #
    # without them, it will compile each run git and does not create/unzip the development files.
    #
    yum -y install which zip unzip
}


require_root

require_requirements

echo 'Installing HyperVM development version.'

if which git >/dev/null; then
	echo 'GIT support detected.'
else
    echo 'No GIT support detected. Installing GIT.'
    install_GIT
fi

ca# Clone from GitHub the last version using git transport (no http or https)
echo "Cleaning up old installs"
rm -Rf /usr/local/lxlabs.bak
mv /usr/local/lxlabs /usr/local/lxlabs.bak
echo "Installing branch $BRANCH from $REPO repository"
git clone -b $BRANCH --single-branch git://github.com/$REPO/hypervm.git ${HYPERVM_PATH}
if [ $? -ne 0 ]; then
echo "Git checkout failed. Exiting."
exit 1;
fi
cd ${HYPERVM_PATH}/hypervm-install
sh ./make-distribution.sh
cd ${HYPERVM_PATH}/hypervm
sh ./make-distribution.sh
printf "Done.\nInstall HyperVM:\ncd ${HYPERVM_PATH}/hypervm-install/hypervm-linux\nsh hypervm-install-master.sh with args\n"