#!/bin/sh
#Download pkgsrc
alias __mk="make install clean clean-depends"
output=`php -v`
output=`expr "$output" : "\(.......\)"`
if !( test "$output" = "PHP 5.3" ) then
(cd /usr/pkgsrc/lang/php53 && __mk)
fi

wget
if ( test $? = 127 ) then
(cd /usr/pkgsrc/net/wget/ &&  __mk)
fi

zip
if ( test $? = 127 ) then
(cd /usr/pkgsrc/archivers/zip/ && __mk)
fi

unzip
if ( test $? = 127 ) then
(cd /usr/pkgsrc/archivers/unzip/ && __mk)
fi

#which

if !( test `pkg_info -e lighttpd` ) then
(cd /usr/pkgsrc/www/lighttpd/ && __mk)
cp /usr/pkg/share/examples/rc.d/lighttpd /etc/rc.d
echo lighttpd=yes>>/etc/rc.conf
/etc/rc.d/lighttpd start
fi

mysql -V
if ( test $? = 127 ) then
(cd /usr/pkgsrc/databases/mysql55-client && __mk)
fi

if !( test `pkg_info -e mysql-server` ) then
(cd /usr/pkgsrc/databases/mysql55-server && __mk)
cp /usr/pkg/share/examples/rc.d/mysqld /etc/rc.d
echo mysqld=yes>>/etc/rc.conf
/etc/rc.d/mysqld start
fi

: '
Block comment
'

#rm -f program-install.zip
#wget http://download.lxlabs.com/download/program-install.zip

#export PATH=/usr/sbin:/sbin:$PATH
#Change this to tar-
#unzip -oq program-install.zip
cd program-install/hypervm-linux
php lxins.php --install-type=master $* | tee hypervm_install.log
