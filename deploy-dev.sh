#!/bin/sh
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2011	LxCenter
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

HYPERVM_PATH='/usr/local/lxlabs'

usage(){
    echo "Usage: $0 [BRANCH] [-h]"
    echo 'BRANCH: master or dev'
    echo 'h: shows this help.'
    exit 1
}

install_GIT()
{
	# Redhat based
	if [ -f /etc/redhat-release ] ; then
		# Install git with curl and expat support to enable support on github cloning
		yum install -y gcc gettext-devel expat-devel curl-devel zlib-devel openssl-devel
	# Debian based
	elif [ -f /etc/debian_version ] ; then
		# No tested
		apt-get install gcc
	fi
	
	# @todo Try to get the lastest version from some site. LASTEST file?
	GIT_VERSION='1.7.9.1'
	
	echo "Downloading and compiling GIT ${GIT_VERSION}"
	wget http://git-core.googlecode.com/files/git-${GIT_VERSION}.tar.gz
	tar xvfz git-*.tar.gz; cd git-*;
	./configure --prefix=/usr --with-curl --with-expat
	make all
	make install
	
	echo 'Cleaning GIT files.'
	cd ..; rm -rf git-*
}

if [ `/usr/bin/id -u` -ne 0 ]; then
    echo 'Please, run this script as root.'
    usage
fi

echo 'Installing HyperVM development version.'

if which git >/dev/null; then
	echo 'GIT support detected.'
else
    echo 'No GIT support detected. Installing GIT.'
    install_GIT
fi

case $1 in 
	master )
		# Clone from GitHub the last version using git transport (no http or https)
		echo "Installing branch hypervm/master"
		mkdir -p ${HYPERVM_PATH}
		git clone git://github.com/lxcenter/hypervm.git ${HYPERVM_PATH}
		cd ${HYPERVM_PATH}
		git checkout master
		cd hypervm-install
		sh ./make-distribution.sh
		cd ../hypervm
		sh ./make-development.sh
		echo "Done. For install run:\ncd ${HYPERVM_PATH}/hypervm-install/hypervm-linux/; sh hypervm-install-[master|slave].sh with args"
		;;
	dev )
		# Clone from GitHub the last version using git transport (no http or https)
		echo "Installing branch hypervm/dev"
		git clone git://github.com/lxcenter/hypervm.git ${HYPERVM_PATH}
		cd ${HYPERVM_PATH}
		git checkout dev
		cd hypervm-install
		sh ./make-distribution.sh
		cd ../hypervm
		sh ./make-development.sh
		echo "Done. For install run:\ncd ${HYPERVM_PATH}/hypervm-install/hypervm-linux/; sh hypervm-install-[master|slave].sh with args"
		;;
	*   )
		usage
		return 1 ;;
esac