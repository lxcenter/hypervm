#!/bin/sh
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2013	LxCenter
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
#
# This file creates hypervm-thirdparty.[version].zip for distribution.
# 
#
# - create zip package
######
#
# Pack:
# httpdocs/thirdparty 
# httpdocs/htmllib/extjs
# httpdocs/htmllib/fckeditor
# httpdocs/htmllib/yui-dragdrop
#
###############################

	echo "################################"
	echo "### Start packaging HyperVM Thirdparty tools."
	echo "### Read version..."
	VERSION=$1
	if [ "$VERSION" == "" ] ; then
	 echo "## Could not read version from commandline, please add a number on commandline"
         echo "## Using current version plus one"
	 CURRENT=`curl --silent http://download.lxcenter.org/download/thirdparty/hypervm-version.list`
         if [ "$CURRENT" == "" ] ; then
         echo "## Could not read version from download center, please add a number on commandline"
	 exit 1
	 fi
        ((CURRENT++))
        VERSION=$CURRENT
	fi
	echo "### Packaging version: hypervm-thirdparty.$VERSION.zip"
	rm -f hypervm-thirdparty.$VERSION.zip

	echo "### Create zip package...";
	zip -qr9 hypervm-thirdparty.$VERSION.zip \
	./httpdocs/thirdparty/ ./httpdocs/htmllib/extjs/ \
	./httpdocs/htmllib/fckeditor/ ./httpdocs/htmllib/yui-dragdrop/ \
	-x \
	"*/CVS/*" \
    "*/.git/*" \
	"*/.svn/*"
	echo "### Finished!"
	echo "################################"
	ls -lh hypervm-thirdparty.*.zip


