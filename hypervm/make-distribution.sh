#!/bin/bash
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
#
#
# This file creates hypervm-[version].zip for distribution.
#
#
# - read version
# - compile c files
# - create zip package
######
echo "################################"
echo "### Start packaging"
echo "### read version..."
# Read version
# Please note, this must be a running (HyperVM installed)  machine (Development/Test/Release server)
if ! [ -f /script/version ] ; then
        echo "## Packaging failed. No /script/version found."
	echo "## Are you sure you are running a development version?"
	echo "### Aborted."
	echo "################################"
        exit
fi
version=`/script/version`
rm -f hypervm-$version.zip

echo "### Compile c files..."
# Compile C files
cd src
make all
make install
cd ../
#
echo "### Create zip package..."
# Package part
zip -r9 hypervm-$version.zip ./src ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO -x \
"*/CVS/*" \
"*/.git/*" \
"*/.svn/*"

echo "### Finished"
echo "################################"
ls -lh hypervm-*.zip
