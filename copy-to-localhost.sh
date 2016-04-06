#!/bin/bash
if [ ! -d "/var/www/html/ebiolabs/question/type/lsciss" ]; then
 mkdir /var/www/html/ebiolabs/question/type/lsciss
fi
cp -r ./* /var/www/html/ebiolabs/question/type/lsciss/