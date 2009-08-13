
 This is primarily meant to help you distribute the vps on different harddisks locations. If you are unsure of what it means, please leave it blank. For openvz, you have to make sure that the locations are absolute paths starting with / and not relative paths. For xen, you can provide lvms in the form lvm:lvmname

 The locations are directories/LVMs where hyperVM will create the vpses. If you add more than 1 location, hyperVM will choose the one with the largest space when creating a vps. The location is basically the harddisk path (LVM or directory), where the vps's main system is created.




