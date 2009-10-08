#!/bin/sh
#
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009     LxLabs
#    Copyright (C) 2009          LxCenter
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

corebackupdir=$1
vmtype=$2
template=$3
vmname=$4
configfile=$5

if [ "x$template" = "x" ] ; then echo "Need Template Name"; exit; fi
if [ "x$corebackupdir" = "x" ] ; then echo "Need Core Backup Dir"; exit; fi
if [ "x$vmname" = "x" ] ; then echo "Need Virtual Machine Name"; exit; fi
if [ "x$configfile" = "x" ] ; then echo "Need Config File Name"; exit; fi

if [ -f /usr/local/bin/gcp ] ; then
	CPPROG=/usr/local/bin/gcp
else 
	CPPROG=cp
fi

mkdir -p $corebackupdir/template
mkdir -p $corebackupdir/template/$vmtype
mkdir -p $corebackupdir/vps
mkdir -p $corebackupdir/vps/$vmtype
mkdir -p $corebackupdir/vps/$vmtype/$vmname/

if ! [ -d $corebackupdir/vps/$vmtype/$vmname/daily.0 ] ; then
 if  [ -d $corebackupdir/template/$vmtype/$template ] ; then 
 	$CPPROG -al $corebackupdir/template/$vmtype/$template $corebackupdir/vps/$vmtype/$vmname/daily.0 ;
 else 
 echo $corebackupdir/template/$vmtype/$template
  	echo no template $template for $vmname
fi
fi

./rsnapshot -v -c $configfile daily

