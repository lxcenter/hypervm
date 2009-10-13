#!/bin/sh


progname=hypervm
source ../bin/common/function.sh


kill_and_save_pid wrapper;
wrapper_main;


