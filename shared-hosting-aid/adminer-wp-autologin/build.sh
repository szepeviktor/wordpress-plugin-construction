#!/bin/bash

# latest plugin.php
wget -O adminer.php "http://www.adminer.org/latest-mysql-en.php"
# latest Adminer
wget -O plugin.php "https://github.com/vrana/adminer/raw/master/plugins/plugin.php"
# concatenate everything
cat plugin.php php-close \
    adminer-wp-login.php php-close \
    wp-autologin-index.php php-close \
    adminer.php \
    > index.php

# optionally remove build files
#rm adminer.php plugin.php