VERSION_FILE=VERSION
VER=`cat $(VERSION_FILE)`

release: init install prepare archive cleanup

install:
	composer require payabbhi/payabbhi-php

init:
	mkdir dist

prepare:
	mkdir payabbhi
	mkdir -p system/library
	mv vendor   system/library
	cp -R admin payabbhi/
	cp -R catalog payabbhi/
	cp -R system payabbhi/
	cp README.md payabbhi/
	cp VERSION  payabbhi/

archive:
	cd payabbhi && zip -r ../payabbhi-opencart-$(VER).zip * && cd ..
	cd payabbhi && tar -cvzf ../payabbhi-opencart-$(VER).tar.gz * && cd ..

cleanup:
	mv payabbhi-opencart-$(VER).zip dist
	mv payabbhi-opencart-$(VER).tar.gz dist
	rm composer.*
	rm -rf payabbhi
	rm -rf system

clean:
	rm -rf dist
