VERSION_FILE=VERSION
VER=`cat $(VERSION_FILE)`

release: init install prepare archive cleanup

install:
	composer require payabbhi/payabbhi-php

init:
	mkdir dist

prepare:
	mkdir payabbhi
	cp -R admin payabbhi/
	cp -R catalog payabbhi/
	mv vendor payabbhi/
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

clean:
	rm -rf dist
