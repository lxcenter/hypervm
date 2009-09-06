all:
	gcc -static -o lxxen lxxen.c
	gcc -static -o lxopenvz lxopenvz.c
	gcc -static -o findfreeloop losetup.c
install: 
	cp lxxen ../../sbin/lxxen
	mv lxxen ../../cexe/lxxen
	cp lxopenvz ../../sbin/lxopenvz
	mv lxopenvz ../../cexe/lxxen
	mv findfreeloop ../../sbin/findfreeloop
 
