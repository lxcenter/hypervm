LIBS="-lssl"

all: hypervm.exe

hypervm.exe: program-backend.o
	gcc -o hypervm.exe program-backend.o ${LIBS}

*.o: 
	gcc -c program-backend.c 
