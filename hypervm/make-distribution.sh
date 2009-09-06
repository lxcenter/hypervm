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
# --> To be created
# <--
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
zip -r9 hypervm-test.zip ./bin ./cexe ./file ./httpdocs ./pscript ./sbin -x \
"*/CVS/*" \
"*/.svn/*"
#
echo "### Finished"
echo "################################"
ls -lh hypervm-*.zip
#

