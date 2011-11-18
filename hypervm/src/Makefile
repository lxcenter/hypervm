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
#

LIBS=-lssl
CC=gcc
CFLAGS=-o

all:
	${CC} ${CFLAGS} lxxen lxxen.c
	${CC} ${CFLAGS} lxopenvz lxopenvz.c
	${CC} ${CFLAGS} findfreeloop losetup.c
	${CC} ${CFLAGS} lxrestart lxrestart.c
	${CC} ${CFLAGS} hypervm.exe program-backend.c ${LIBS}

install: 
	cp lxxen ../sbin/lxxen
	mv lxxen ../cexe/lxxen
	cp lxopenvz ../sbin/lxopenvz
	mv lxopenvz ../cexe/lxopenvz
	cp lxrestart ../sbin/lxrestart
	mv lxrestart ../cexe/lxrestart
	mv findfreeloop ../sbin/findfreeloop
	cp hypervm.exe ../sbin/hypervm.exe.core
	mv hypervm.exe ../sbin/hypervm.exe

.PHONY: clean

clean:


