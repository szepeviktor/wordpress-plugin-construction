#!/bin/bash
#
# Configure export-one-db
#

echo '# Download phpMyAdmin'
# For MySQL 5.0
#/usr/local/src/debian-server-tools/package/phpmyadmin-get-http-old.sh || exit 1
/usr/local/src/debian-server-tools/package/phpmyadmin-get.sh || exit 1

echo '# Copy included files'
pushd phpMyAdmin-*-english || exit 2
# For MySQL 5.0
#cat ../exp-o-pma-includes-4.0.x.txt|xargs -I {} cp -a --parents {} ../ || exit 3
cat ../exp-o-pma-includes.txt|xargs -I {} cp -a --parents {} ../ || exit 3
popd || exit 4
rm -rf phpMyAdmin-*-english phpMyAdmin-*-english.tar.xz || exit 5

echo '# Generate 2048 bit RSA encryption key'
openssl genpkey -algorithm rsa -pkeyopt rsa_keygen_bits:2048 -out exp-o-private.key || exit 6
openssl rsa -in exp-o-private.key -pubout -out exp-o-public.pem || exit 7

echo '# Setup configuration file'
which pwgen &>/dev/null || exit 8
IV=$(pwgen 16 1)
SECRET=$(pwgen 30 1)
sed -i "s/'????????????????'/'${IV}'/" exp-o-config.php || exit 9
sed -i "s/'??????????????????????????????'/'${SECRET}'/" exp-o-config.php || exit 10

echo '# Remote access'
read -p "Enter user agent sting: " UA || exit 11
sed -i "s/<UA>/${UA//\//\\\/}/" .htaccess || exit 12
IP="$(/sbin/ifconfig | grep -m1 -w -o 'inet addr:[0-9.]*' | cut -d':' -f2)"
read -e -p "Enter management server IP: " -i "$IP" MGMNT || exit 13
sed -i "s/<IP-REGEXP>/${MGMNT//./\\\\.}/" .htaccess || exit 14
sed -i "s/<IP>/${MGMNT}/" .htaccess || exit 15

echo '# Clean up'
rm README.md exp-o-pma-includes.txt exp-o-util.sh

echo "Don't upload exp-o-private.key!!!"
echo
echo "Save IV and private key."
echo -e "IV:\n${IV}"
cat exp-o-private.key
echo

echo '# Upload directory'
echo "$RANDOM" | md5sum | cut -d" " -f1

echo '# How to backup'
echo "wget -q -S --content-disposition --user-agent='${UA}' --header='X-Secret-Key: ${SECRET}' 'http://---/export-one-db.php'"
