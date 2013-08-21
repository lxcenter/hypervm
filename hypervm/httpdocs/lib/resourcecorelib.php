<?php 

abstract class Resourcecore extends Lxclient {

static $__desc_backup_flag =  array("q", "",  "allow_backing_up");
static $__desc_backupschedule_flag =  array("q", "",  "allow_backup_schedule");
static $__desc_client_num =  array("q", "",  "clients:number_of_clients");
static $__desc_vps_num =  array("q", "",  "vps:number_of_vps");
static $__desc_disk_usage	 = array("q", "",  "disk:disk_quota_(MB)");
static $__desc_backup_num = array("q", "",  "backup:number_of_backups");
static $__desc_pserver_num =  array("q", "",  "servers:number_of_servers");

static $__desc_memory_usage	 = array("q", "",  "burst:burstable_memory_(MB)_(openvz_only)");
static $__desc_cpu_usage	 = array("q", "",  "cpu:cpu_usage_(%) 100/CPU");

static $__desc_cpuunit_usage	 = array("q", "",  "cpuUNIT:CPU_UNITS_(default_1000)");
static $__desc_ncpu_usage	 = array("q", "",  "cpuNum:number_of_CPUS");
static $__desc_ioprio_usage	 = array("q", "",  "ioprio:IO_priority_(0-7)");

//static $__desc_kernelmem_usage	 = array("q", "",  "Kernel_Memory_(KB)");

static $__desc_process_usage	 = array("q", "",  "process:number_of_processes");
static $__desc_guarmem_usage	 = array("q", "",  "guar:guaranteed_memory_(MB)(openvz_only)");
static $__desc_realmem_usage	 = array("q", "",  "memory:memory_usage(MB)(xen_only)");
static $__desc_traffic_usage	 = array("q", "",  "traffic:traffic_(MB/month)");
static $__desc_iptables_flag =  array("q", "",  "enable_iptables_(only_for_openvz)");
static $__desc_swap_usage	 = array("q", "",  "swap:swap_(MB)");
static $__desc_vswap_flag =  array("q", "",  "enable_vswap");


static $__desc_logo_manage_flag =  array("q", "",  "can_change_logo");
static $__desc_vpspserver_list = array("Q", "",  "VM_server_pool");
static $__desc_traffic_last_usage	 = array("D", "",  "Ltraffic:traffic_usage_for_last_month_(MB)");
//static $__desc_monitorserver_num =   array("q","",  "mon_serv:number_of_monitored_servers");
static $__desc_monitorport_num =   array("q","",  "mon_port:number_of_monitored_ports");
static $__desc_uplink_usage = array("q", "",  "uplink:uplink_traffic(KB/s)");
static $__desc_vps_add_flag = array("q", "",  "can_add_vps");
static $__desc_managedns_flag = array("q", "",  "can_manage_dns");
static $__desc_managereversedns_flag = array("q", "",  "can_manage_reverse_dns");
static $__desc_rebuildvps_flag = array("q", "",  "can_rebuild_vps");
static $__desc_centralbackup_flag = array("q", "",  "enable_central_backup");
static $__desc_vps_limit_flag = array("q", "",  "can_change_limit_of_vps");
static $__desc_ip_manage_flag = array("q", "",  "can_manage_ip");
//static $__desc_vmipaddress_a_num	 = array("q", "",  "ip:number_of_ipaddresses");
static $__desc_secondlevelquota_flag	 = array("q", "",  "second_level_quota_(only_for_openvz)");

}

