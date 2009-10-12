<?PHP
//
//    HyperVM, Server Virtualization GUI for OpenVZ and Xen
//
//    Copyright (C) 2000-2009     LxLabs
//    Copyright (C) 2009          LxCenter
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
?>

<?php 

include_once "htmllib/lib/include.php"; 


error_reporting(E_ALL);

$uname = trim(`uname -r`);
system("mkinitrd -f --with=xennet --builtin=aic7xxx --builtin=serverworks --preload=xenblk --omit-raid-modules --omit-lvm-modules --fstab={$sgbl->__path_program_root}/file/sysfile/xen/fstab  /boot/lxxen-initrd.img $uname");

system("cd /boot ; ln -sf vmlinuz-$uname hypervm-xen-vmlinuz ; ln -sf lxxen-initrd.img hypervm-xen-initrd.img");
