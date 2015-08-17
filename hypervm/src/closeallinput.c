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
#include <stdlib.h>
#include <unistd.h>
#include <string.h>

int
main(int argc, char **argv)
{
  int i=0;
  char cmd[500];
  
  memset(cmd, 0,500);
  
  for(i=3; i<1024; i++)
  {
    close(i);
  }

  for (i=1; i<argc; i++)
  {
    strcat(cmd, argv[i]);
    strcat(cmd, " ");
  }
  return system(cmd);

}
