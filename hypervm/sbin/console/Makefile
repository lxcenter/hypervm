all:
	gcc -static -o lxxen lxxen.c
	gcc -static -o lxopenvz lxopenvz.c
	gcc -static -o findfreeloop losetup.c
	gcc -static -o lxrestart lxrestart.c
	
install: 
	cp lxxen ../../sbin/lxxen
	mv lxxen ../../cexe/lxxen
	cp lxopenvz ../../sbin/lxopenvz
	mv lxopenvz ../../cexe/lxopenvz
	cp lxrestart ../../sbin/lxrestart
	mv lxrestart ../../cexe/lxrestart
	mv findfreeloop ../../sbin/findfreeloop

