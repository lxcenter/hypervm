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


veidmark='envID:'
namemark='Name:'

function getveid()
{
    local pid=$1

		[ -f /proc/${pid}/status ] || return
		cat /proc/${pid}/status | \
		awk -v pid=${pid} 'BEGIN{veid=0} /^'${namemark}'|^'${veidmark}'/{
			if ($1 == "'${namemark}'") {
				name = $2;
			} else if ($1 == "'${veidmark}'") {
				veid = $2;
			}
		}
	END{
		printf("%s\n", veid);
	}'
}
										
getveid $1
