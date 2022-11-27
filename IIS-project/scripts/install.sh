#!/bin/sh

cd ..
composer install
cd ../WWW
php -S 127.0.0.1:8000
