#!/bin/sh
#
# This file creates binaries.
# 
#
######
echo "################################"
echo "### Start compiling"
# Compile C files
# Part 1
cd sbin/console
make ; make install
cd ../../
# Part 2
cd bin/common
make ; make install
cd ../../
echo "### Finished"
echo "################################"
#
