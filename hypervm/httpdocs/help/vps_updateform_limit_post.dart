 
 Please note that for Xen, you need to reboot the vps for the disk quota to change. For openvz it is done in real time. Memory allocation doesn't need reboot for either Xen or openvz. You can rate limit the traffic by changing the value of 'outbout traffic' from Unlimited to a particular value.

 CPU percentage is calculated at 100% per cpu. So if you have two cpus, the total cpu power you have is 200, which can be distributed across the virtual machines. Please note that the CPU limit is the hard limit and is not burstable.
