#!/bin/bash
#
# (c) Simon Rowe, 2013
# (c) Karanbir Singh, 2013
#
# This file come from Xen4CentOS project
# (http://wiki.centos.org/Manuals/ReleaseNotes/Xen4-01) 
# and it is call only for CentOS 6

default=$(grubby --default-kernel)
kver=$(expr $default : '.*vmlinuz-\(.*\)')
[ -n "$kver" ] || exit 0
initrd=$(grubby --info $default | sed -ne 's/^initrd=//p')
new-kernel-pkg --install --package kernel --multiboot=/boot/xen.gz "--mbargs=dom0_mem=1024M,max:1024M loglvl=all guest_loglvl=all" --initrdfile=$initrd $kver
exit $?
