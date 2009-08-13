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
# This script runnning on HN and perform next postinstall tasks:
# 1. Randomizes crontab for given VE so all crontab tasks
#  of all VEs will not start at the same time.
# 2. Disables root password if it is empty.
#

function randcrontab()
{
	file=${VE_ROOT}"/etc/crontab"

	[ -f "${file}" ] || return 0

	/bin/cp -fp ${file} ${file}.$$
	cat ${file} | awk '
BEGIN { srand(); }
{
	if ($0 ~ /^[ \t]*#/ || $0 ~ /^[ \t]+*$/) {
		print $0;
        	next;
	}
	if ((n = split($0, ar, /[ \t]/)) < 7) {
		print $0;
		next;
	}
	# min
	if (ar[1] ~ /^[0-9]+$/) {
		ar[1] = int(rand() * 59);
	} else if (ar[1] ~/^-\*\/[0-9]+$/) {
		r = int(rand() * 40) + 15;
		ar[1] = "-*/" r;
	}
	# hour
	if (ar[2] ~ /^[0-9]+$/) {
		ar[2] = int(rand() * 6);
	}
	# day
	if (ar[3] ~ /^[0-9]+$/) {
		ar[3] = int(rand() * 31) + 1;
	}
	line = ar[1];
	for (i = 2; i <= n; i++) {
		line = line " "  ar[i];
	}
	print line;
} 
' > ${file}.$$ && /bin/mv -f ${file}.$$ ${file}
	/bin/rm -f ${file}.$$ 2>/dev/null
}

function disableroot()
{
	file=${VE_ROOT}"/etc/passwd"

	[ -f "$file" ] || return 0

	if /bin/grep -q "^root::" "${file}" 2>/dev/null; then
		/bin/sed 's/^root::/root:!!:/g' < ${file} > ${file}.$$ && \
			/bin/mv -f ${file}.$$ ${file}
		/bin/rm -f ${file}.$$ 2>/dev/null
	fi
}

[ -z "${VE_ROOT}" ] && return 1
randcrontab 
disableroot

exit 0
