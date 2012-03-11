<?php
/*
 *    HyperVM, Server Virtualization GUI for OpenVZ and Xen
 *
 *    Copyright (C) 2000-2009    LxLabs
 *    Copyright (C) 2009-2012    LxCenter
 *
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$__information['updateform_limit_pre'] = " If the quota fields are Left blank, the maximum possible values will be automatically used ";
$__information['updateform_selfbackupconfig_pre'] = " This is primarily meant to configure the remote backup of the master database and nothing else. The database dump is taken everyday and is saved in a local folder in this machine. If an ftp account is configured here, the file will be uploaded to the machine. This is mainly useful in large cluster setup where the failure of master can have much larger impacts.";
$__information['updateform_generalsetting_pre'] = " The 'HelpDesk Url' is a link to your Helpdesk, which will be used in place of the default help desk built into the software. Community Url is the link the client will see on his left page, and in normal cases can point to your forum.";
$__information['updateform_login_options_pre'] = " Note: session timeout cannot be less than 100 and if less, will be automatically set to 100.";
$__information['lxbackup_updateform_backup_pre'] = " The backup file will appear in the __backup directory of your client area. You can access it by clicking on the 'File Manager' Tab.";
$__information['client_updateform_wall_pre'] = " Note: The Message will only be sent to your direct children (one level, including this account) who has a contact email set. ";
$__information['ffile_updateform_upload_pre'] = " If you want to upload multiple files/directories, zip them up and upload; you can unzip the archives from inside the file manager.  ";
$__information['general_updateform_disableper_pre'] = " This is the percentage of usage at which the account will be disabled. The normal value is 110%. You will be given warnings when the quota reaches 90,100,110%. ";
$__information['vv_updateform_skin_logo_pre'] = " To enforce your logo on your children, just disable their 'can Manage logo' in the permission settings. The logo URL can be a fully qualified Internet url of the form http://. So to have your own logo, just upload the url to a location and give the location here.";
$__information['updateform_upload_logo_pre'] = " Leave the fields blank to reset the logos to default images. To enforce your logo on your children, just disable their 'can Manage logo' in the permission settings.  ";
$__information['xen_restart_message'] = "Please note that you have to restart the Xen VM for the changes to take affect. This is necessary only for Xen VMs. OpenVZ changes are affected immediately.</font> ";
$__information['updateform_createtemplate_pre'] = "The initial three words of the new os Image has to properly reflect the distro and version, and thus they are automatically generated from the Os Image of this vps. You can supply the fourth string, and it can denote the type of OS Image this represents.";
$__information['vps_updateform_rebuild_pre'] = "<font color=red> Not: Mevsut VPS tamamen silinecek. Bu işlemi geri almanız mümkün değildir </font> ";
$__emessage['blocked'] = "Adres Bloklu";
$__emessage['no_server'] = "Sunucuya Bağlanılamıyor.";
$__emessage['set_emailid'] = "Lütfen Eposta adresinizi hatasız giriniz ";
$__emessage['no_socket_connect_to_server'] = "Sunuya bağlanılamıyor [%s]. Please login and restart hypervm service on this server";
$__emessage['quota_exceeded'] = "Kota aşılmış [%s]";
$__emessage['license_no_ipaddress'] = "The public ipaddress [%s] of this server was not found in in the license repository. Please contact Lxlabs sales or your reseller to create a license for this server. </a> ";
$__emessage['you_have_unread_ticket'] = "You have [%s] Unread Ticket(s).";
$__emessage['you_have_unread_message'] = "You have [%s] Unread Message(s).";
$__emessage['already_exists'] = "The resource of name [%s] already exists.";
$__emessage['file_already_exists'] = "Dosya [%s] Zaten mevcut.";
$__emessage['contact_set_but_not_correct'] = "Eposta adresiniz geçersiz. Buraya <url:a=updateform&sa=information> tıklayarak </url> düzeltebilirsiniz.";
$__emessage['contact_not_set'] = "İrtibat bilgileriniz geçersiz. BUraya <url:a=updateform&sa=information> tıklayarak </url> düzeltebilirsiniz.";
$__emessage['security_warning'] = "Your password is now set as a generic password which constitutes a grave security threat. Please change it immediately";
$__emessage['system_is_updating_itself'] = "The system at this point is upgrading itself, and thus you won't be able to make any changes for a few minutes. All read actions work normally though.";
$__emessage['template_not_owner'] = "Bu şablonun sahibisiniz";
$__emessage['ipaddress_changed_amidst_session'] = "IP Address Changed Amidst Session.";
$__emessage['more_than_one_user'] = "BU hesaba giriş yapmış birden çok kullanıcı var. Buraya  <url:a=list&c=ssession>tıklayarak </url> listeleyebilirsiniz.";
$__emessage['login_error'] = "Login Başarısız";
$__emessage['file_exists'] = "Dosya [%s] Mevcut. ";
$__emessage['nouser_email'] = "Eposta adresi ve irtibat adresi birbirinden farklı";
$__emessage['session_expired'] = "Oturum bitti";
$__emessage['e_password'] = "Şİfre yanlış";
$__emessage['is_demo'] = "[%s] İnaktif.";
$__emessage['user_create'] = "Kullanıcı [%s] Oluşturulamıyor lütfen farklı bir isim girin";
$__emessage['backup_has_been_scheduled'] = "Şu an yedekleme gerçekleşiyor. İşlem tamamlandığında bir mesaj alacaksınız.";
$__emessage['a_vps_with_the_same_id_exists'] = "A vps with the id [%s] already exists on the system. This either means this was created outside of hyperVM or else it got orphaned because hyperVM was interrupted forcibly in the midst of a migration. You can either a) Login manually and delete the vps. or b) Use our import facility to import this vps into hyperVM. Sorry for the inconvenience.";
$__emessage['could_not_start_vps'] = "VPS başlatılamıyor, Sebep: [%s]";
$__emessage['vps_is_locked_by_another_user'] = " VPS [%s] Başka bir kullanıcı tarafından kilitlenmiş. This would also be because you had interrupted an earlier activity that would take long time to complete. HyperVM has waited 15 seconds for the other process to finish. Please try after sometime. ";
$__emessage['to_use_filemanager_shut_down_xen_vm_first'] = " Dosya yöneticisi sadece VPS kapalı iken kullanılabilir.";
$__emessage['rebuild_time_limit'] = " Lütfen yeniden yapılandırmadan önce [%s] dakika bekleyin. ";
$__emessage['vpsid_already_exists_on_another_server'] = "The vpsid [%s] exists on another server.";
$__smessage['switch_done'] = "Switching the Servers has been run in the background. You will be sent a mail when the switch is complete.";
$__smessage['mis_changed'] = "Görünüm ayarları güncellendi.";
$__smessage['password_sent'] = "Şifre resetlendi ve gönderildi.";
$__smessage['restore_has_been_scheduled'] = "Geri kurma işlemi arka planda tamamlanıyor. İşlem tamamlanınca bir eposta alacaksınız.";
$__smessage['vps_creation_in_background'] = "Vpses are getting created in the background. Just periodically refresh this page, and you can see them listed.";
$__smessage['kloxo_installation_started'] = "Kloxo Installation has been started in the VPS. The log is kept in a file called HyperVM-kloxo_install.log in the / directory, which you can view using the File Manager";
;
$__information['vps_updateform_rebuild_pre'] = "<font color=red> Note: The existing Vps will be completely destroyed and built anew. You will lose all data that is currently in there. </font> ";
$__information['vps_updateform_installkloxo_pre'] = "<font color=red> Note: Installation of Kloxo basically involves a complete rebuild of the vps with a fresh ostemplate containing kloxo, and thus your current system will be completely destroyed and rebuilt. The Url for kloxo is https://ip:7777 and CP login/password for kloxo is admin/admin.</font> ";
$__information['emailalert_addform__pre'] = "These are the extra email addresses that are also intimated by the port monitoring system when a service goes down. Your actual contact email can be found under the [b] information [/b] tab";
