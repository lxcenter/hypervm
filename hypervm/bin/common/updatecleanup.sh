#!/bin/sh
#
#    HyperVM, Server Virtualization GUI for OpenVZ and Xen
#
#    Copyright (C) 2000-2009     LxLabs
#    Copyright (C) 2009          LxCenter
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

/bin/cp htmllib/filecore/php.ini /usr/local/lxlabs/ext/php/etc/
exec /usr/local/lxlabs/ext/php/php ../bin/common/tmpupdatecleanup.php "$@"
# i am chmoding sbin to 755 inside updatecleanup so needs to do this here.
chmod 755 ../sbin/lxrestart
chmod ug+s ../sbin/lxrestart
