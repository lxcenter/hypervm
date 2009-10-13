#include "lxlogin.c"

int main()
{
	char *s;
	s = get_vm_name();
	putenv("PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/bin");
	execlp("vzctl", "vzctl", "enter", s, NULL);
}

