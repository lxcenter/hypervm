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

$__information['updateform_limit_pre'] = " Si lo deja en blanco, automáticamente se activará con la Cuota máxima posible";
$__information['allowedip_addform__pre'] = " Puede agregar direcciones IP en la forma [b] 192.168.1.*[/b]  que identifica una serie. En vez de poner un número, puede utilizar un asterisco [b] * [/b]  que representa una serie completa. También puede agregar direcciones uno por uno en la forma 192.168.1.2. Es obligatoria la notación con tres puntos y cuatro números. Si quiere permitir todas, entonces bora todas e introduzca [b] *.*.*.* [/b]. No otras notaciones son permitidos actualemente.";
$__information['blockedip_addform__pre'] = " Es la lista de direcciones IP bloqueadas. La notación IP es igual que la lista direcciones IP permitidas. Solo deberías agregar direcciones bloqueadas cuando lista de direcciones permitidas está vacía. Si la lista permitida no esta vacía, será negado el acceso automáticamente a las direcciones no listadas. ";
$__information['updateform_selfbackupconfig_pre'] = " Es para configurar la copía de seguridad remota de la base de datos principal y nada más. La copia se hace a diario y se graba en una carpeta local de la máquina.  Con la configuración FTP la copia será grabado en la máquina remota. Resultará útil solamente para grandes sistemas donde la corupción de la base de datos tendría mayor impacto.";
$__information['updateform_generalsetting_pre'] = " El 'URL Soporte' es para indicar su propio Sistema de Sorpote a su medida cuando no quiere el sistema integrado. El URL Foro, enlazará con su sistema de Foro de comunidad que aparecerá en el menú izquierdo del panel.";
$__information['updateform_download_config_pre'] = "Si activas esta funcion, el HyperVM enruta el fichero por medio del maestro. La funcion de descargar por medio del maestro es solamente útil cuando los esclavos tienen direcciones IP de red local y no puedes accederlas desde fuera. Con la funcion desactivada, con la descarga de ficheros el maestro crea una sesión temporal y redirige el navegador directamente al esclavo. Es para ahorrar ancho de banda";
$__information['updateform_login_options_pre'] = " Nota: El tiempo máximo de sesión no puede ser menos que 100. Con menos, automáticamente se pondrá en mínimo 100.";
$__information['lxbackup_updateform_backup_pre'] = " El archivo de la copia de seguridad aparecerá en la carpeta __backup del cliente (/home/lxadmin/client/nombrecliente/__backup). Puedes aceder a la carpeta pulsando en la pestaña 'Gestor de Ficheros'. Para restaurar una copia de seguridad, hace falta primero subir el archivo al servidor con la pestaña [b]Subir[/b]. Puede subir directamente o desde un URL HTTP o un servidor FTP. Luego tiene que volver aquí para seleccionar el archivo pulsando el ícono carpeta que está al lado del campo marcado [b]Desde Archivo[/b]. Luego pulsar [b]start restore process[/b] para empezar el proceso de restauración.";
$__information['phpini_updateform_edit_admin_pre'] = "Hace falta activar [b] Manage Php Configuration [/b] para permitir que Lxadmin gestione tu php.ini por completo. ¡Ojo!, LxAdmin sobreescribirá tu php.ini anteriior. Para restaurar tu antiguo php.ini, tienes que desactivar [b] Manage Php Configuration [/b] y actualiar. Recomendamos que LxAdmin completamente gestione tu configuración PHP. ";
$__information['client_updateform_wall_pre'] = " Nota: El mensaje solemente se enviará a cuentas 'hijas' que tienen introducido su dirección de contacto (en Información). ";
$__information['ffile_updateform_upload_pre'] = " El Gestor de ficheros sabe descomprimir archivos ZIP. Si quiere subir carpetas enteras of multiples ficheros, entonces mejor subirlos en un ZIP y luego descomprimirlos en el gestor.";
$__information['dskshortcut_a_list__pre'] = " Aquí aparecen en la lista las páginas favoritas que has añadido pulsando en enlace [b] add to favorites [/b] que aparece por debajo de Ayuda y Salir en cada página. Pulsa en uno de la lista para personalizar su nombre.";
$__information['ticketconfig_updateform_ticketconfig_pre'] = " [b]Mailgate[/b] es una cuenta de correo donde <%program%> descargará correos y los incluirá en el sistema de soporte de clientes. Cuando <%program%> envía correos, utilizará la dirección [b] cuenta correo [/b] como dirección de orígen. [b]Servidor De Correos[/b] es donde descargara su correo debe ser del tipo POP.";
$__information['updateform_mysqlpasswordreset_pre'] = "Solamente debes utilizarlo si has perdido la contreseña root. Normalemente puedes cambiarlo <url:a=list&c=dbadmin>aquí (PHPMyAdmin)</url>. ¡Ten paciencia hasta que acabe la operación!, porque tardará un poco. Esto realizará la operación por medio de la opción 'skip-grant-tables'. Si no funciona, por favor, abre una sessión SSH y ejecuta ../bin/common/misc/reset-mysql-root-password.php ";
$__information['general_updateform_disableper_pre'] = " Esto determina en que porcentaje del uso de los recursos debe deshabilitarse la cuenta. El valor normal sería 110%. Se envian avísos a los 90,100 y 110%. ";
$__information['vv_updateform_skin_logo_pre'] = " Para obligar que aparezca tu logo en las cuentas subordinarios, sencillamente deshabilita 'Puede cambiar logotipo' en la configuración de Límites. ";
$__information['pserver_updateform_information_pre'] = "FQDN (Fully Qualified Domain Name) es un dato muy importante y debe ser el dominio debidamente delegado en la dirección IP de su máquina. Cuando se establezca, a partir de entonces hyperVM lo utilizará para todas las comunicaciones de red y si el FQDN fuera incorrecto, la comunicación entre maestro y esclavo fallaría. Si lo dejas en blanco, hyperVM utilizará la primera dirección IP asignada. Debe ser un nombre de dominio que vale desde cualquier punto de la red.";
$__information['pserver_updateform_backupconfig_pre'] = "Dirección IP de Red Interno (local) será utilizado para hacer copias de seguridad (por FTP). Algunos Centros de Datos proveen estás direcciones para evitar congestión de la red principal. La IP será utilizado por el servidor de copias de seguridad para conectar con con este nodo cuando realiza una copia del VPS.";
$__information['pserver_addform__pre'] = "Si acabas de instalar un servidor esclavo (VPS/VM) la contraseña por defecto es 'admin'. Asegurate de cambiarlo después cuanto antes.";
$__information['updateform_upload_logo_pre'] = " Deja los campos en blanco para volver a los imágenes por defecto. Para obligar que aparezca tu logo en las cuentas subordinarios, sencillamente deshabilita 'Puede cambiar logotipo' en la configuración de Límites.  ";
$__information['xen_restart_message'] = "Los cambios entrarán en vigor solamente después de rearrancar una Máquina Virtual Xen. No es necesario para un Servidor Privado Virtual OpenVZ.</font> ";
$__information['updateform_createtemplate_pre'] = "Las 3 primeras palabras del nuevo imágen SO identifican correctamente la distribución y su versión porque son generado por el imágen mismo. Tu puedes definir la cuarta para denominar el tipo de imágen.";
$__information['vps_updateform_rebuild_pre'] = "<font color=red> Nota: El actual VPS será destruido y reconstruido desde zero. Perderás todos los datos que contiene.</font> ";
$__information['vps_updateform_mainipaddress_pre'] = "Esto reorganizará la configuración de las direcciones IP, poniendo la dirección principal como primera. Es útil para programas que necesitan especificar una dirección principal para que funcione la licencia. Además, cuando se agrega otra dirección IP, hace falta reiniciar la dirección IP principal otra vez.";
$__information['vps_updateform_fixdev_pre'] = "Es para arreglar un problema que surge cuando se realiza una actualización hacia CentOS 4.5. La actualización borrará todos las entradas /dev/ y daja al VPS inoperable. Está función sacará nuevas entradas dev de un archivo tar para solucionar el problema.";
$__information['vps_updateform_installlxadmin_pre'] = "<font color=red> Nota: La instalación de Lxadmin implicará una reconstrucción completa del VPS con una imágen OS que incluye Lxadmin y destruyendo así el sistema actual por completo. El URL de Lxadmin será https://ip:7777 y para abrir una sessión inicial debe utilizar usuario/contraseña admin y admin.</font> ";
$__information['vps_addform_openvz_pre'] = "A VPS is a fully self contained login system. You need not create a separate client; the owner of the vps can login to the Control Panel using the vps name and password. You should create a client only if he has more than one vps. A vps owner is a complete client system with its own help desk, login history etc.";
$__information['vps_addform_xen_pre'] = "Cada VPS tiene su propio panel de control y no hace falta crear un cliente para manejarlo, el usuario puede abrir una sessión con como nombre de usuario el nombre del VPS y la contraseña. Solo deberías crear un cliente si tiene mas que un VPS. Cada usuario tiene a su disposición un sistema complete con Soporte y historial de sesiones etc.";
$__information['lxadmin_show__pre'] = " Para que funcione, hace falta tener instalado hyperVM versión > 1.4.3052 y Lxadmin version > 4.3.7462. Si utiliza esto por primera vez, asegurate de reiniciar una vez el VPS por medio del panel de control para permitir que hyperVM configure los parametros de aceso seguro.";
$__information['emailalert_addform__pre'] = "Estas son direcciones de correo-e adicionales para avisar cuando monitorización detecta servicio parado. Tu dirección de contacto actual encuentras en la pestaña [b] información [/b]";
$__emessage['blocked'] = "Tu dirección está bloqueada";
$__emessage['no_server'] = "No he podido conectar con el servidor.";
$__emessage['set_emailid'] = "Establezca una identidad Email válido, por favor";
$__emessage['no_socket_connect_to_server'] = "No he podido conectar al servidor [%s]. Por favor abre sessión y reinicia el servicio lxadmin en este servidor";
$__emessage['restarting_backend'] = "Reiniciando  servidor de servicios de fondo(backend). Intenta de nuevo en 30 segundos, por favor.";
$__emessage['quota_exceeded'] = "Sobrepasó cuota de [%s]";
$__emessage['license_no_ipaddress'] = "La dirección pública [%s] del servidor no se ha encontrado en el repositorio de licencias. Contanta el departamento de ventas de Lxlabs o tu revendedor para crear una licencia de este servidor. </a> ";
$__emessage['you_have_unread_ticket'] = "Tienes [%s] incidencia(s) sin leer.  <burl:a=list&c=ticket> Pulsa para leer. </burl>";
$__emessage['you_have_unread_message'] = "Tienes [%s] mensaje(s) sin leer. <burl:a=list&c=smessage> Pulsa aquí para leer. </burl>";
$__emessage['already_exists'] = "Ya existe el recurso con el nombre [%s].";
$__emessage['file_already_exists'] = "Ya existe el fichero [%s].";
$__emessage['contact_set_but_not_correct'] = "Tu Información de Contacto tiene una dirección Email incorrecta. <url:a=updateform&sa=information> Pulas para modificar.</url> ";
$__emessage['contact_not_set'] = "Información de Cotacto incorrecta. <url:a=updateform&sa=information>Pulsa para solucionar. </url> ";
$__emessage['security_warning'] = "Aún tienes establecido la contraseña por defect, que constituye un grave problema de seguridad. Por favor cambiala cuanto antes.";
$__emessage['system_is_updating_itself'] = "El sistema se está actualizando y no permitirá cambios por unos minutos. Aún funcionan todas las operaciones de lectura.";
$__emessage['template_not_owner'] = "No eres el propietario de esta plantilla";
$__emessage['ipaddress_changed_amidst_session'] = "Cambió la dirección IP en medio de la sesión, que probablemente significa un intento Secuestro de Sessión.";
$__emessage['more_than_one_user'] = "Se abrió sessión en esta cuenta a mas de un usuario. <burl:a=list&c=ssession>Pulsa para ver la lista de sesiones. </burl> ";
$__emessage['login_error'] = "Fallaba abrir sessión";
$__emessage['file_exists'] = "fichero(s) [%s] ya existe(n). No se pegara...";
$__emessage['cannot_unzip_in_root'] = "No puedes decomprimir ZIP en la raíz. Elije un directorio y luego intenta de nuevo.";
$__emessage['nouser_email'] = "El Email no es igual a la dirección en la Información de contacto del usuario.";
$__emessage['session_expired'] = "Sesión caducada";
$__emessage['e_password'] = "Contraseña Incorrecta";
$__emessage['is_demo'] = "[%s] desactivado en la versión Demostración.";
$__emessage['user_create'] = "No se ha podido crear el usuario [%s]. Intenta de nuevo con otro nombre de usuario";
$__emessage['backup_has_been_scheduled'] = "Ya se está creando la copia de seguridad en segundo plano. Recibirás un correo electrónico de aviso cuando haya terminado la operación.";
$__emessage['old_lxadmin_found'] = "Ya se encuentra una instalación de Lxadmin en el VPS. Si quieres instalar de verdad, tienes que borrar el lxadmin-install-master.sh y /usr/local/lxlabs/lxadmin con el explorador de ficheros.";
$__emessage['a_vps_with_the_same_id_exists'] = "Un VPS con el id [%s] ya existe en el sistema. Esto siginfica que fue creado fuera de HyperVM o se ha hecho \"huerfano\" por una interrupción forzada en el proceso de migración. Puedes: a) Abrir una sesión SSH e intentar borrarlo manualemente o b) Utilizar el herramienta de importación para importar el VPS en hyperVM. Perdona las molestias.";
$__emessage['could_not_start_vps'] = "No se arrancó el VPS, Razón: [%s]";
$__emessage['vps_is_locked_by_another_user'] = " El VPS [%s] está bloqueado por otro usuario. Para no interrumpir otro proceso HyperVM ha esperado 15 seconds pero aún sigue activo. Por favor, inténtalo mas tarde. ";
$__emessage['to_use_filemanager_shut_down_xen_vm_first'] = " Solo puedes utilizar el Gestor de Ficheros cuando la Máquina Virtual está apagada y puedes utilizarlo para arreglar cosas que impiden que arranque. ";
$__emessage['rebuild_time_limit'] = " Espera [%s] minutos antes de reconstruir de nuevo. ";
$__emessage['vpsid_already_exists_on_another_server'] = "El ID VPS [%s] ya existe en otro servidor.";
$__smessage['switch_done'] = "El cambio de servidores es un proceso secundario. Te avisaremos por correo-e cuando haya terminado.";
$__smessage['mis_changed'] = "Configuración de Visualización cambiado correctamente.";
$__smessage['password_sent'] = "La nueva contreseña se ha enviado correctamiente.";
$__smessage['restore_has_been_scheduled'] = "La Restauración es un proceso de secundario. Te avisarémos por correo-e cuando haya terminado.";
$__smessage['vps_creation_in_background'] = "Los VPS se crean en un proceso secundario. Periodicamente pulsa en Actualizar para verlos listados.";
$__smessage['lxadmin_installation_started'] = "La instalación de Lxadmin en el VPS ha empezado. El proceso mantiene un registro de sus actividades en /HyperVM-lxadmin_install.log que puedes visualizar con el Gestor de Ficheros.";