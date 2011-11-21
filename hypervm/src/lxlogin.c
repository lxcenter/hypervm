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


#include "lxlogin.h"

char *get_vm_name(void)
{
	uid_t uid;
	char *s, *name;

	struct passwd * pwd;

	uid = getuid();

	pwd = getpwuid(uid);

	if (!pwd) {
		fprintf(stderr, "Could Not find the User\n");
		_exit(8);
	}

	s = pwd->pw_gecos;
	name = strstr(s, " for ");

	if (!name) {
		fprintf(stderr, "Could Not find the virtual Machine Name\n");
		_exit(9);
	}

	s = name + strlen(" for ");

	setuid(0);
	seteuid(0);
	setgid(0);
	setegid(0);

	return s;
}
