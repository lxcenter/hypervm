#include <stdio.h>
#include <string.h>
#include <ctype.h>
#include <fcntl.h>
#include <errno.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/ioctl.h>
#include <sys/stat.h>
#include <sys/mman.h>
#define dev_t int
#include <linux/loop.h>


int find_unused_loop_device(char **ret)
{
	/* Just creating a device, say in /tmp, is probably a bad idea -
	   people might have problems with backup or so.
	   So, we just try /dev/loop[0-7]. */

	char dev[20];
	char *loop_formats[] = { "/dev/loop%d", "/dev/loop/%d" };
	int i, j, fd, somedev = 0, someloop = 0, loop_known = 0;
	struct stat statbuf;
	struct loop_info loopinfo;
	FILE *procdev;
	for (j = 0; j < 2; j++) {
		for(i = 0; i < 256; i++) {
			sprintf(dev, loop_formats[j], i);
			if (stat (dev, &statbuf) == 0 && S_ISBLK(statbuf.st_mode)) {
				somedev++;
				fd = open (dev, O_RDONLY);

				if (fd >= 0) {
					if(ioctl (fd, LOOP_GET_STATUS, &loopinfo) == 0) {
						someloop++;             /* in use */
					} else if (errno == ENXIO) {
						//perror("");
						close (fd);
						*ret = strdup(dev);
						return 1;
					}
					close (fd);
				}

				/* continue trying as long as devices exist */
				continue;
			}
			*ret = strdup(dev);
			return 0;
		}
	}
}


int main()
{
	int status;
	char *device;
	status = find_unused_loop_device(&device);
	printf("%s\n", device);

	if (status) {
		exit(0);
	} else {
		exit(10);
	}
}

