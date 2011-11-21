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


#include "losetup.h"

int find_unused_loop_device(char **ret)
{
	/* Just creating a device, say in /tmp, is probably a bad idea -
	   people might have problems with backup or so.
	   So, we just try /dev/loop[0-7]. */

	char dev[20];
	const char *loop_formats[] = { "/dev/loop%d", "/dev/loop/%d" };
	int i, j, fd, somedev = 0, someloop = 0; /* loop_known = 0 unused */
	struct stat statbuf;
	struct loop_info loopinfo;

	/* FILE *procdev; Unused var? Seems to be only dev? */

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

	return 0;
}

int main()
{
	int status;
	char *device;

	status = find_unused_loop_device(&device);
	printf("%s\n", device);

	if (status) {
		exit(EXIT_SUCCESS);
	} else {
		exit(10);
	}

	return EXIT_SUCCESS;
}
