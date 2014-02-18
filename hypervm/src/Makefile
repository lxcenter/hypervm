#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009	LxLabs
#    Copyright (C) 2009-2011	LxCenter
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU Affero General Public License as
#    published by the Free Software Foundation, either version 3 of the
#    License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU Affero General Public License for more details.
#
#    You should have received a copy of the GNU Affero General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
PREFIX=lx
LIBS=-I/usr/include -lssl
CC=gcc
DEBUG = -g
CFLAGS = -Wall -O2 -std=c99 -ansi -pedantic -Wextra -Werror -Wunused \
-Wimplicit -Wshadow -Wformat=2 -Wmissing-declarations   \
-Wno-missing-prototypes -Wwrite-strings                 \
-Wbad-function-cast -Wnested-externs -Wcomment -Winline \
-Wchar-subscripts -Wcast-align -Wno-format-nonliteral   \
-Wsequence-point -Wdeclaration-after-statement $(DEBUG)
LFLAGS = -Wall -c $(DEBUG)

xen:
	${CC} ${CFLAGS} ${PREFIX}login.c ${PREFIX}xen.c -o ${PREFIX}xen
	
openvz:
	${CC} ${CFLAGS} ${PREFIX}login.c ${PREFIX}openvz.c -o ${PREFIX}openvz
	
freeloop:
	${CC} ${CFLAGS} losetup.c -o findfreeloop 
	
restart:
	${CC} ${CFLAGS} ${PREFIX}restart.c -o ${PREFIX}restart 

backend:
	${CC} ${CFLAGS} -o hypervm.exe program-backend.c ${LIBS}

closeallinput:
	${CC} ${CFLAGS} -o closeallinput closeallinput.c ${LIBS}
	

all: xen openvz freeloop restart backend closeallinput

install: 
	cp ${PREFIX}xen ../sbin/lxxen
	mv ${PREFIX}xen ../cexe/lxxen
	
	cp ${PREFIX}openvz ../sbin/lxopenvz
	mv ${PREFIX}openvz ../cexe/lxopenvz
	
	cp ${PREFIX}restart ../sbin/lxrestart
	mv ${PREFIX}restart ../cexe/lxrestart
	
	mv findfreeloop ../sbin/findfreeloop
	
	cp hypervm.exe ../sbin/hypervm.exe.core
	mv hypervm.exe ../sbin/hypervm.exe

	mv closeallinput ../cexe/closeallinput

clean:
	\rm -rf *.o *~ ${PREFIX}xen ${PREFIX}openvz ${PREFIX}restart findfreeloop hypervm.exe closeallinput