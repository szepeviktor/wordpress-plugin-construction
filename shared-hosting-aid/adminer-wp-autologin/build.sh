#!/bin/bash

# latest Adminer (mysql-only, english-only)
wget -O adminer.php "http://www.adminer.org/latest-mysql-en.php"
# latest plugin.php
wget -O plugin.php "https://github.com/vrana/adminer/raw/master/plugins/plugin.php"

# concatenate everything
cat plugin.php php-close \
    adminer-wp-login.php php-close \
    wp-autologin-index.php php-close \
    adminer.php \
    > wp-adminer.php || exit 1

# remove build files on success
rm adminer.php plugin.php
