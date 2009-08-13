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
# This script sets up resolver inside VE
# For usage info see vz-veconfig(5) man page.
#
# Some parameters are passed in environment variables.
# Required parameters:
#   SEARCHDOMAIN
#       Sets search domain(s). Modifies /etc/resolv.conf
#   NAMESERVER
#       Sets name server(s). Modifies /etc/resolv.conf
function set_dns()
{
	local cfgfile="$1"
	local server="$2"
	local search="$3"
	local srv

	if [ -n "${search}" ]; then
		put_param2 "${cfgfile}" search "${search}"
	fi
	if [ -n "${server}" ]; then
		[ -f ${cfgfile} ] || touch ${cfgfile}
		sed "/nameserver.*/d" < ${cfgfile} > ${cfgfile}.$$ && \
			mv -f ${cfgfile}.$$ ${cfgfile} || \
			error "Can't change file ${cfgfile}" ${VZ_FS_NO_DISK_SPACE} 
		for srv in ${server}; do
			echo "nameserver ${srv}" >> ${cfgfile}
		done
	fi
	chmod 644 ${cfgfile}
}


set_dns /etc/resolv.conf "${NAMESERVER}" "${SEARCHDOMAIN}"

exit 0
