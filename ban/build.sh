#!/bin/bash

WPF2B=~/wordpress-plugin-construction/wordpress-fail2ban

[ -r ban.php ] || exit 1

cp -f ${WPF2B}/block-bad-requests/wp-fail2ban-bad-request-instant.inc.php ./block-bad-requests/

cat ${WPF2B}/miniban/miniban-base.php > ./miniban/wp-miniban-htaccess.inc.php
cat ${WPF2B}/miniban/miniban-htaccess.php \
    | sed '/^<?php$/d' >> ./miniban/wp-miniban-htaccess.inc.php

cp -f ${WPF2B}/mu-plugin/wp-fail2ban-mu-instant.php ./mu/
