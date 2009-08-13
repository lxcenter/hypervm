#include "lxlogin.c"

int main()
{

	char *s;
	s = get_vm_name();
	putenv("PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/bin");
	printf("Logging into Xen Virtual machine %s, Press Ctrl-] to Quit. Press a couple of Enters to start.\n", s);
	execlp("xm", "xm", "console", s, NULL);


}
