#!/bin/bash
#  Copyright (C) 2000-2006 SWsoft. All rights reserved.
#
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#
# This script configure IP alias(es) inside Gentoo like VE.
#
# Parameters are passed in environment variables.
# Required parameters:
#   IP_ADDR       - IP address(es) to add
#                   (several addresses should be divided by space)
# Optional parameters:
#   VE_STATE      - state of VE; could be one of:
#                     starting | stopping | running | stopped
#   IPDELALL	  - delete all old interfaces
#
ETH_DEV=eth0

FAKEGATEWAY=${NETWORK_GATEWAY}
FAKEGATEWAYNET=${NETWORK_GATEWAY_NET}

IFCFG_DIR=/etc/conf.d
IFCFG=${IFCFG_DIR}/net

SCRIPT=/etc/runlevels/default/net.${ETH_DEV}

HOSTFILE=/etc/hosts

function get_cidr()
{
	local netmasks=(
	255.255.255.255 255.255.255.254 255.255.255.252 255.255.255.248 255.255.255.240 255.255.255.224 255.255.255.192 255.255.255.128
	255.255.255.0 255.255.254.0 255.255.252.0 255.255.248.0 255.255.240.0 255.255.224.0 255.255.192.0 255.255.128.0
	255.255.0.0 255.254.0.0 255.252.0.0 255.248.0.0 255.240.0.0 255.224.0.0 255.192.0.0 255.128.0.0
	255.0.0.0 254.0.0.0 252.0.0.0 248.0.0.0 240.0.0.0 224.0.0.0 192.0.0.0 128.0.0.0
	)

	local i=31
	local cidr=0
	while [ $i -ge 0 ]; do
        	let cidr+=1
                if [ "${netmasks[$i]}" = "$1" ]; then
                        local cidr_notation=$cidr
              	fi
                let i-=1
	done            
        echo $cidr_notation
}


function fix_net()
{
	[ -f "${SCRIPT}" ] && return 0
	rc-update del net.eth0 &>/dev/null
#ln -sf /etc/init.d/net.lo /etc/init.d/net.${ETH_DEV}
	rc-update add net.lo boot &>/dev/null
	rc-update add net.${ETH_DEV} default &>/dev/null
	if ! grep -qe "^config_eth" ${IFCFG} 2>/dev/null; then
		return 0
	fi
	cp -pf ${IFCFG} ${IFCFG}.$$ || error "Unable to copy ${IFCFG}"
	sed -e 's/^config_eth/#config_eth/' -e 's/^routes_eth/#routes_eth/' < ${IFCFG} > ${IFCFG}.$$ && mv -f ${IFCFG}.$$ ${IFCFG} 2>/dev/null
	if [ $? -ne 0 ]; then
		rm -f ${IFCFG}.$$ 2>/dev/null
		error "Unable to create ${IFCFG}"
	fi
}

function setup_network()
{
	fix_net
	put_param3 ${IFCFG} "config_${ETH_DEV}" ""
	# add fake route
#put_param3 ${IFCFG} "routes_${ETH_DEV} -net ${FAKEGATEWAYNET}/24" # dev ${ETH_DEV}

	#check if gw is already set up
	cat ${IFCFG} | grep ${FAKEGATEWAY}\" &>/dev/null
	local gw_check=$?
        if [ "$gw_check" -gt 0 ]; then
		add_param3 ${IFCFG} "routes_${ETH_DEV}" "default via ${FAKEGATEWAY}"
	fi
	# Set up /etc/hosts
	if [ ! -f ${HOSTFILE} ]; then
		echo "127.0.0.1 localhost.localdomain localhost" > $HOSTFILE
	fi
}

function add_ip()
{
        local ip
	local new_ips

	# In case we are starting VE
	if [ "x${VE_STATE}" = "xstarting" ]; then
#setup_network
		echo nothing
	fi

	setup_network
	if [ "x${IPDELALL}" = "xyes" ]; then
		put_param3 "${IFCFG}" "config_${ETH_DEV}" ""
	fi

	cidr=`get_cidr ${MAIN_NETMASK}`
	add_param3 "${IFCFG}" "config_${ETH_DEV}" "${MAIN_IP_ADDRESS}/${cidr}"

	for ip in ${IP_ADDR}; do
		grep -qw "${ip}" ${IFCFG} ||  add_param3 "${IFCFG}" "config_${ETH_DEV}" "${ip}/${cidr}"
	done

	if [ "x${VE_STATE}" = "xrunning" ]; then
		# synchronyze config files & interfaces
#/etc/init.d/net.${ETH_DEV} restart 
			echo Nothing
	fi
}

add_ip
exit 0
# end of script
