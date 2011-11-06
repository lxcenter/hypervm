#!/bin/sh
#
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009    LxLabs
#    Copyright (C) 2009-2011    LxCenter
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
#
# This file creates hypervm-install.zip for distribution.
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
"../hypervm-install/.git/*" \
"../hypervm-install/hypervm-linux/CVS/*" \
"../hypervm-install/hypervm-linux/.svn/*" \
"../hypervm-install/hypervm-linux/.git/*"
####
echo "### hypervm-install.zip created."
echo "##########################################"
####
ls -l hypervm-install.zip
####
