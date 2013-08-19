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
#	 author: Krzysztof Taraszka <krzysztof.taraszka@gmail.com>
#
#    Install and deploy a local development version on a local environment
#

HYPERVM_PATH='/usr/local/lxlabs'

require_root()
{
	if [ `/usr/bin/id -u` -ne 0 ]; then
    	echo 'Please, run this script as root.'
    	usage
	fi
}

require_root

echo 'Installing HyperVM local development version.'

yum install -y make gcc openssl-devel zip unzip

cd hypervm-install
sh ./make-distribution.sh
cd ../hypervm
sh ./make-development.sh
echo "Done. For install run:"
echo "cd hypervm-install/hypervm-linux/; sh hypervm-install-[master|slave].sh with args"
echo ""
