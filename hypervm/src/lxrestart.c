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

/*
	HyperVM wrapper. Allows the php script - which runs as HyperVM user to execute
	programs that need root privileges.
	In the production version, error messages should be completely avoided,
	the program failing without giving any reason for the failure, so as to
	discourage the curious.

*/

#include "lxrestart.h"

int main(int argc, char **argv)
{
	int uid, gid, suid, sgid, euid;
	char buf[BUFSIZ];
	struct passwd *pwd;
	int debug = 0;

	/*
	 Setting it to large value initially. So that,
 	 if finding the uer was unsucccesful, it doesn't default to root.
 	 */
	uid = gid = suid = sgid = euid = 10001;

	uid = getuid();
	gid = getgid();
	pwd = getpwuid(uid);
	suid = sgid = 0;

	if (!pwd || (strcmp(pwd->pw_name, "lxlabs") && strcmp(pwd->pw_name, "root"))) {
		/*
		   To be removed before deployment. The production version shouldn't print anything.
		   It should just exit without displaying any errors. This error message is only
		   for debugging purposes
		 */
		if (debug) {
			fprintf(stderr, "%s: Not allowed to execute\n", pwd->pw_name);
		}
		exit(0);
	}

	/* 3 arguments are expected. '-u' 'user', the command ... the rest are arguments. */
	if (argc < 2)  {
		exit(0);
	}

	setgid(0);
	setegid(0);
	setuid(0);
	seteuid(0);
	
	snprintf(buf, BUFSIZ - 1, "/etc/init.d/%s backendrestart", argv[1]);
	return system(buf);
}
