<?php 

include_once "htmllib/lib/include.php"; 


error_reporting(E_ALL);

$uname = trim(`uname -r`);
system("mkinitrd -f --with=xennet --builtin=aic7xxx --builtin=serverworks --preload=xenblk --omit-raid-modules --omit-lvm-modules --fstab={$sgbl->__path_program_root}/file/sysfile/xen/fstab  /boot/lxxen-initrd.img $uname");

system("cd /boot ; ln -sf vmlinuz-$uname hypervm-xen-vmlinuz ; ln -sf lxxen-initrd.img hypervm-xen-initrd.img");
