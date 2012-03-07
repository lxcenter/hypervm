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
#    Run the unit test
#

usage(){
    echo "Usage: $0 [BRANCH] [-h]"
    echo 'h: shows this help.'
    exit 1
}

require_root()
{
	if [ `/usr/bin/id -u` -ne 0 ]; then
    	echo 'Please, run this script as root.'
    	usage
	fi
}

require_PHPunit()
{
	if which phpunit >/dev/null; then
		echo 'PHPunit support detected.'
	else
	    echo 'No PHPunit support detected. Installing PHPunit.'
	    install_PHPunit
	fi
}

install_PHPunit()
{
	# Redhat based
	if [ -f /etc/redhat-release ] ; then
		yum install -y php53 php53-devel php-pear php53-xml
	# Debian based
	elif [ -f /etc/debian_version ] ; then
		apt-get install -y php-devel php-pear php-xml
	fi
	
	pear channel-discover pear.phpunit.de
	pear channel-discover pear.symfony-project.com
	pear channel-discover components.ez.no
	pear update-channels
	pear upgrade-all
	pear upgrade --force PEAR
	pear install --alldeps phpunit/PHPUnit
	
	# Only CentOS (it doesn't provide a php-unit binary)
	if [ -f /etc/redhat-release ] ; then
		cp -rf ./php-unit-centos /usr/bin/phpunit
		chmod +x /usr/bin/phpunit
	fi
	
	# Copy a version of lxphp for php-unit
	cp -rf ./lxphp-unit-centos /usr/bin/lxphpunit
	chmod +x /usr/bin/lxphpunit
}

require_root
require_PHPunit

# Use this for normal test using native PHP version
phpunit --colors unit/GeneralUnitTest.php

# Use this for normal test using lxphp PHP version
#lxphpunit --colors unit/GeneralUnitTest.php