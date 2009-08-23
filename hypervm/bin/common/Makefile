# be verbose!
# incase you dont want to see the messages, uncomment the other RM_FLAGS line
# and comment the one with "-v"
RM_FLAGS="-v"
#RM_FLAGS=""
LIBS="-lssl"
CC="gcc"

all: hypervm.exe

hypervm.exe: program-backend.o
	gcc -o hypervm.exe program-backend.o ${LIBS}

install: hypervm.exe
	cp hypervm.exe ../../sbin/hypervm.exe.core
	mv hypervm.exe ../../sbin/hypervm.exe	

clean:
	rm ${RM_FLAGS} -f program-backend.o ../../sbin/hypervm.exe ../../sbin/hypervm.exe.core hypervm.exe

*.o: 
	${CC} -c program-backend.c 

.PHONY: all clean
