#!/bin/sh
#
# This file creates hypervm-[version].zip for distribution from SVN.
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
# Please note, this must be a running machine with SVN version!
if ! [ -f /script/version ] ; then
        echo "## Packaging failed. No /script/version found."
	echo "## Are you sure you are running a development version?"
	echo "### Aborted."
	echo "################################"
        exit
fi
version=`/script/version`
rm -f hypervm-$version.zip
#
echo "### Compile c files..."
# Compile C files
# Part 1
cd sbin/console
make ; make install
cd ../../
# Part 2
cd bin/common
make ; make install
cd ../../
#
echo "### Create zip package..."
# Package part
zip -r9 hypervm-$version.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin ./RELEASEINFO -x \
"*/CVS/*" \
"*/.svn/*"
#
echo "### Finished"
echo "################################"
ls -lh hypervm-*.zip
#

