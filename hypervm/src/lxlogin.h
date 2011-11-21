/*
    HyperVM, Server Virtualization GUI for OpenVZ and Xen

    Copyright (C) 2000-2009	LxLabs
    Copyright (C) 2009-2011	LxCenter

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


#define _POSIX_SOURCE
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>
#include <stdlib.h>
#include <pwd.h>

#define OPENVZ_BINARY "vzctl"
#define XEN_BINARY    "xm"
#define SEARCH_PATHS  "PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/bin"

/* Avoid Debian warnings "implicit-function-declaration" */
int setegid(gid_t);
int seteuid(uid_t);
int putenv(const char *);

#ifndef _GET_VM_NAME_
	#define _GET_VM_NAME_
	char *get_vm_name();
#endif
