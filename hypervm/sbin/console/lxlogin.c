#include <stdio.h>
#include <sys/types.h>
#include <unistd.h>
#include <pwd.h>


char *get_vm_name()
{
	int uid;
	char *s, *name;

	struct passwd * pwd;

	
	uid = getuid();

	pwd = getpwuid(uid);

	if (!pwd) {
		printf("Could Not find the User\n");
		_exit(8);
	}

	s = pwd->pw_gecos;
	name = strstr(s, " for ");

	if (!name) {
		printf("Could Not find the virtual Machine Name\n");
		_exit(9);
	}

	s = name + strlen(" for ");

	setuid(0);
	seteuid(0);
	setgid(0);
	setegid(0);

	return s;
}
