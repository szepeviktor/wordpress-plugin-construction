#!/bin/bash

which apg &> /dev/null || exit 99

wp_safe_salt() {
    apg -a 1 -n 1 -m 64 -M SNCL -E "'\"\\"
}

printf "define( 'AUTH_KEY',         '%s' );\n" "$(wp_safe_salt)"
printf "define( 'SECURE_AUTH_KEY',  '%s' );\n" "$(wp_safe_salt)"
printf "define( 'LOGGED_IN_KEY',    '%s' );\n" "$(wp_safe_salt)"
printf "define( 'NONCE_KEY',        '%s' );\n" "$(wp_safe_salt)"
printf "define( 'AUTH_SALT',        '%s' );\n" "$(wp_safe_salt)"
printf "define( 'SECURE_AUTH_SALT', '%s' );\n" "$(wp_safe_salt)"
printf "define( 'LOGGED_IN_SALT',   '%s' );\n" "$(wp_safe_salt)"
printf "define( 'NONCE_SALT',       '%s' );\n" "$(wp_safe_salt)"
