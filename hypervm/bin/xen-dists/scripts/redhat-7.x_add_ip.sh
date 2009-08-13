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
# This script configure IP alias(es) inside RedHat like VE.
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
ETH_DEV_CFG=ifcfg-$ETH_DEV

FAKEGATEWAY=${NETWORK_GATEWAY}
FAKEGATEWAYNET=${NETWORK_GATEWAY_NET}

IFCFG_DIR=/etc/sysconfig/network-scripts
IFCFG=${IFCFG_DIR}/ifcfg-${ETH_DEV}
NETFILE=/etc/sysconfig/network
HOSTFILE=/etc/hosts

function fix_ifup()
{
	file="/sbin/ifup"

	[ -f "${file}" ] || return 0
	[ "x${VE_STATE}" != "xstarting" ] && return 0

	if grep -q 'if \[ "\${DEVICE}" = "lo" \]; then' ${file} 2>/dev/null
	then
		cp -fp ${file} ${file}.$$ || return 1
		/bin/sed -e 's/if \[ "\${DEVICE}" = "lo" \]; then/if \[ "${IPADDR}" = "127.0.0.1" \]; then/g' < ${file} > ${file}.$$ && \
			mv -f ${file}.$$ ${file}
		rm -f ${file}.$$ 2>/dev/null
	fi
}

function setup_network()
{
	local routefile=/etc/sysconfig/network-scripts/route-${ETH_DEV}

	# Set up eth0 main interface as 127.0.0.1
	mkdir -p ${IFCFG_DIR}
	echo "DEVICE=${ETH_DEV}
ONBOOT=yes
IPADDR=127.0.0.1
NETMASK=255.255.255.0
NETWORK=${FAKEGATEWAYNET}
BROADCAST=0.0.0.0" > $IFCFG || error "Can't write to file $IFCFG" $VZ_FS_NO_DISK_SPACE

	# Set /etc/sysconfig/network
	put_param $NETFILE NETWORKING yes
	put_param $NETFILE GATEWAY ${FAKEGATEWAY}

	# Set up /etc/hosts
	if [ ! -f ${HOSTFILE} ]; then
		echo "127.0.0.1 localhost.localdomain localhost" > $HOSTFILE
	fi
	fix_ifup
}

function create_config()
{
	local ip=$1
	local ifnum=$2

	echo "DEVICE=${ETH_DEV}:${ifnum}
IPADDR=${ip}
NETMASK=255.255.255.255" > ${IFCFG_DIR}/bak/${ETH_DEV_CFG}:${ifnum} || \
	error "Unable to create interface config file" ${VZ_FS_NO_DISK_SPACE}
}

function get_all_aliasid()
{
	IFNUM=-1
	
	cd ${IFCFG_DIR} || return 1
	IFNUMLIST=`ls -1 bak/${ETH_DEV_CFG}:* 2>/dev/null | \
		sed "s/.*${ETH_DEV_CFG}://"`
}

function get_aliasid_by_ip()
{
	local ip=$1
	local idlist

	cd ${IFCFG_DIR} || return 1
	IFNUM=`grep -l "IPADDR=${ip}$" ${ETH_DEV_CFG}:* | head -n 1 | \
		sed -e 's/.*:\([0-9]*\)$/\1/'`
}

function get_free_aliasid()
{
	local found=

	[ -z "${IFNUMLIST}" ] && get_all_aliasid
	while test -z ${found}; do
		let IFNUM=IFNUM+1
		echo "${IFNUMLIST}" | grep -q -E "^${IFNUM}$" 2>/dev/null || \
			found=1
	done
}

function backup_configs()
{
	local delall=$1

	rm -rf ${IFCFG_DIR}/bak/ >/dev/null 2>&1
	mkdir -p ${IFCFG_DIR}/bak
	[ -n "${delall}" ] && return 0

	cd ${IFCFG_DIR} || return 1
	if ls ${ETH_DEV_CFG}:* > /dev/null 2>&1; then
		cp -rf ${ETH_DEV_CFG}:* ${IFCFG_DIR}/bak/ || \
			error "Unable to backup intrface config files" ${VZ_FS_NO_DISK_SPACE}
	fi
}

function move_configs()
{
	cd ${IFCFG_DIR} || return 1
	rm -rf ${ETH_DEV_CFG}:*
	mv -f bak/* ${IFCFG_DIR}/ >/dev/null 2>&1 
	rm -rf ${IFCFG_DIR}/bak
}

function add_ip()
{
	local ip
	local new_ips 

	# In case we are starting VE
	if [ "x${VE_STATE}" = "xstarting" ]; then
		# Remove all ETH config files
		rm -f ${IFCFG_DIR}/${ETH_DEV_CFG}:* >/dev/null 2>&1
		setup_network
	fi
	backup_configs ${IPDELALL}
	new_ips="${IP_ADDR}"
	if [ "x${IPDELALL}" = "xyes" ]; then
		new_ips=
		for ip in ${IP_ADDR}; do
			get_aliasid_by_ip "${ip}"
			if [ -n "${IFNUM}" ]; then
				# ip already exists just create it in bak
				create_config "${ip}" "${IFNUM}"
			else
				new_ips="${new_ips} ${ip}"
			fi
		done
	fi
	for ip in ${new_ips}; do
		get_free_aliasid
		create_config "${ip}" "${IFNUM}"
	done
	move_configs
	if [ "x${VE_STATE}" = "xrunning" ]; then
		# synchronyze config files & interfaces
		cd /etc/sysconfig/network-scripts && ./ifup-aliases ${ETH_DEV}
	fi
}

add_ip
exit 0
# end of script
