
 [b] Name [/b] Name of the ippool

 [b] First Ipaddress [/b] First ipaddress of the ip block (For instance 192.168.1.3)

 [b] Last Ipaddress [/b] Last ipaddress of the ip block (For instance 192.168.1.100)

 So this ippool will automatically contain ipaddresses from 192.168.1.3 to 192.168.1.100.

 [b] Resolv Entries [/b] The Resolv Entries for this ippool. Whenever a vps is created, the vps's Resolv Entries will be taken from the ippool from which its ip is taken. Resolv Entries is actually the ipaddresses that will be added to the /etc/resolv.conf file.

 [b] Gateway [/b] Only needed for xen, but it is the network gateway for the vps when its ip is grabbed from this pool.

 [b] Servers This is Applicable to [/b] This means that this particular ippool will be used when you add a vps to one of the servers listed here.

 Once you have an ippool, then adding a vps becomes very trivial, since you don't have to bother about remembering ipaddress. You can just set the number of ipaddresses for a vps, and hyperVM will automatically assign it one from the ippool for the server on which that vps is created.
