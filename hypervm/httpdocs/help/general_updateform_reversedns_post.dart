
 To configure the dns, you have to first add your dns servers to the hyperVM cluster. Then go to [b] server home [/b] -> [b] server roles [/b] and add 'dns' to the list. Please note that in the case of openvz, you cannot run any service on the host, since the rhel scripts use killproc, and since the processes of the vpses are visible on the host node, restarting bind in the main node, will automatically kill all the binds running in all the vpses. So the DNS server has to be either a xen main node, or if you are on openvz, then it has to be a vps. If you don't want the this particular slave to act as vps server, then you can remove the 'vps' from the list. Once a server/vps has been assigned the role of dns, then it will appear in the list for [b] 'servers the dns entries are configured' [/b] field. The [b] primary, secondary dns servers [/b] are the names of your primary and secondary nameservers, for instanance [b] ns.lxlabs.com, ns1.lxlabs.com [/b].

 Once you enable the dns here, the 'reverse/forward dns' button will automatically appear inside the [b] vps home [/b], and the client will be able to add the dns entry for his ip address. The information will be automatically sent to the slave servers you have configured here.

 Steps:

  * The DNS should be installed in a VPS. This is a must, since it won't work in the node. Install hyperVm inside a vps with --virtualization-type=NONE. Install bind, bind-chroot packages. yum -y install bind bind-chroot

  * Then add this vps to hyperVM cluster. Install hypervm as slave with --virtualization-type=NONE on the vps and add it from the [b] server list [/b] -> [b] add server [/b] page. 

  * Add [b] dns [/b] to the list of the server roles. Go to [b] server home -> server roles [/b] and then add 'dns' to the list.

  * Come back here and you will see the server in the [b] available [/b] list for [b] servers the dns are configured on [/b]. You can add the server to the selected list.

  * The [b] reverse/forward dns [/b] button will automatically appear in the [b] vps home [/b] in the client, and the clients will be able to add the reverse/forward dns.


 The fields:

 [b] Primary DNS [/b]: ns.lxlabs.com
 [b] Secondary DNS [/b] ns1.lxlabs.com
 [b] slaves the dns entries are synced on [/b]: The slave servers in the cluster where you are hosting the reverse/forward dns.

