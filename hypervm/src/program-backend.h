/*
    HyperVM, Server Virtualization GUI for OpenVZ and Xen

    Copyright (C) 2000-2009	LxLabs
    Copyright (C) 2009-2011	LxCenter

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <wait.h>
#include <netdb.h>
#include <unistd.h>
#include <sys/select.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <dirent.h>
#include <openssl/ssl.h>
#include <openssl/crypto.h>
#include <openssl/err.h>

/* Avoid Debian warnings "implicit-function-declaration" */
void    bzero(void *, size_t);
int     snprintf (char *s, size_t size, const char *template, char *argv);
int     mkstemp(char *template);
pid_t   wait3(int *, int, struct rusage *);
struct tm *localtime_r(const time_t * arestrict, struct tm *brestrict);
int     alphasort(const struct dirent **, const struct dirent **);
int     scandir(const char *, struct dirent ***,
                int (*)(const struct dirent *),
                int (*)(const struct dirent **,
                const struct dirent **));

int     ssl_or_tcp_read(SSL *ssl, int sock, char * buf, int n);
int     run_php_prog_ssl(SSL *ssl, int sock);
int     ssl_or_tcp_write(SSL *ssl, int sock, char * buf, int n);
SSL_CTX * ssl_init();
char    tcp_create_socket(short int s_port);
char    * ssl_sock_read(int sock, SSL_CTX *ctx);
int     tcp_sock_read(int sock);
int     accept_and(int listen_sock);
void    ssl_or_tcp_fork(int listen_socket, SSL_CTX *ctx);
int     close_and_system(const char *cmd);
int     process_timed(int counter);
int     exec_scavenge();
int     process_timed_in_child();

#define SCAVENGE_TIME_FILE "../etc/conf/scavenge_time.conf"

#define RSA_SERVER_CERT     "/usr/local/lxlabs/kloxo/file/backend.crt"
#define RSA_SERVER_KEY          "/usr/local/lxlabs/kloxo/file/backend.key"

#define RSA_SERVER_CA_CERT "server_ca.crt"
#define RSA_SERVER_CA_PATH   "sys$common:[syshlp.examples.ssl]"

#define MASTER 0
#define SLAVE 1
#define ON 1
#define OFF 0

#define MAX(x,y) if ((x) > (y)) return x; else return y;

#define RETURN_NULL(x) if ((x)==NULL) exit(1)
#define RETURN_ERR(err,s) if ((err)==-1) { perror(s); exit(1); }
#define RETURN_SSL(err) if ((err)==-1) { ERR_print_errors_fp(stderr); exit(1); }

int global_type;
