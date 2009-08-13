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
# This script deletes IP address inside VE for Slackware like systems.
#
# Parameters are passed in environment variables.
# Required parameters:
#   IP_ADDR       - IPs to delete, several addresses should be divided by space
# Optional parameters:
#   IPDELALL      - deleet all ip addresses
IFCFG=/etc/rc.d/rc.inet1

function del_ip()
{
	local ip

	[ -f ${IFCFG}  ] || return 0
	for ip in ${IP_ADDR}; do
		if grep -wq "${ip}" ${IFCFG} 2>/dev/null; then
#/sbin/ifconfig eth0 down
			put_param ${IFCFG} "IPADDR" ""
			break
		fi
	done
}

del_ip
exit 0
# end of script
