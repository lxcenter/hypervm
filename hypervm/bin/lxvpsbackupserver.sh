#!/bin/sh

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

