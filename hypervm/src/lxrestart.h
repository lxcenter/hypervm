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
#include <pwd.h>
#include <string.h>
#include <stdlib.h>
#include <sys/types.h>

/* Avoid Debian warnings "implicit-function-declaration" */
int   execvp(const char *file, char *const argv[]);
int   setenv(const char *, const char *, int);
uid_t getuid(void);
gid_t getgid(void);
int   setegid(gid_t);
int   seteuid(uid_t);
int   setgid(gid_t);
int   setuid(uid_t);
int   snprintf (char *s, size_t size, const char *template, char *argv);
