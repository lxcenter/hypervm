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


#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <stdint.h>
#include <ctype.h>
#include <fcntl.h>
#include <errno.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/ioctl.h>
#include <sys/stat.h>
#include <sys/mman.h>

/* Avoid error "specifier-qualifier-list" on 2.6.18 kernels (Centos) */
#define __u64 int

#include <linux/loop.h>

/* Avoid Debian warnings "implicit-function-declaration" */
char  *strdup(const char *);
int   setenv(const char *, const char *, int);
int   execvp(const char *file, char *const argv[]);
uid_t getuid(void);
gid_t getgid(void);
int   setenv(const char *, const char *, int);

int find_unused_loop_device(char **ret);
