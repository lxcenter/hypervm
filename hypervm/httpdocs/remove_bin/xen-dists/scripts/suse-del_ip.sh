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
# This script deletes IP alias(es) inside VE for SuSE-9.
# For usage info see ve-alias_del(5) man page.
#
# Parameters are passed in environment variables.
# Required parameters:
#   IP_ADDR       - IPs to delete, several addresses should be divided by space
# Optional parameters:
#   VE_STATE      - state of VE; could be one of:
#                     starting | stopping | running | stopped
ETH_DEV=eth0
IFCFG_DIR=/etc/sysconfig/network/
IFCFG="${IFCFG_DIR}/ifcfg-${ETH_DEV}"

function del_ip()
{
	local ip ids id

	for ip in ${IP_ADDR}; do
		ids=`grep -E "^IPADDR_.*=${ip}$" ${IFCFG_DIR}ifcfg-eth0 | sed 's/^IPADDR_\(.*\)=.*/\1/'`
		for id in ${ids}; do
			sed -e "/^IPADDR_${id}=/{
				N
				/$/d
}" < ${IFCFG} > ${IFCFG}.bak && mv -f ${IFCFG}.bak ${IFCFG}
#		ifconfig  ${ETH_DEV}:${id} down 2>/dev/null
		done
	done
}

del_ip

exit 0
# end of script
