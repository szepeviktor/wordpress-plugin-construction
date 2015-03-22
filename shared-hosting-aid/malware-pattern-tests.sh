#!/bin/bash

# Variable named functions
#
# $malware = 'ev' . strtolower( 'AL' );
# $malware('code to execute');
# $malware ('space before opening parenthesis');
# $malWware25 ('UPPERCASE and digits');
#TODO new line in $func \n ( ...

grep "\$[a-zA-Z0-9_]\+\s*(" "$0" \
    | wc -l | grep -qx "3" && echo "OK." || echo "Failed: Variable named functions"

# Iframes and scripts
#
# <iframe src="phishing.site" />
# <iframe without="src" />
# <Iframe src="UPPERCASE" />
# <iframe	src="tab.separated" />

grep -i '<iframe\s.*src=\|<script\s.*src=' "$0" \
    | wc -l | grep -qx "3" && echo "OK." || echo "Failed: Iframes and scripts"

# Dangerous functions
#
# eval('malware');
# evAl('uppercase-malware');
# evAl   ('spaces before opening parenthesis');
# base64('AABBB');
# bAse64('UPPER==');
# base64   ('SPACES=');
# not_str_rot13('SPACES=');
# str_rot13   ('edoc');
# uudecode('abc1%');

grep -i '\W\(uudecode\|str_rot13\|eval\|base64\)\s*(' "$0" \
    | wc -l | grep -qx "8" && echo "OK." || echo "Failed: Dangerous functions"

# Miscellaneous signatures
#
# @define('WSO_VERSION', '2.5.1');
# <?$m='malware';
# <?php malware(); ?><?php valid('code');
# preg_replace('/(.*)/e', 'print( "Hello, world!".PHP_EOL)', '' );

grep -i 'WSO\|<?\$\|?><?php\|\Wpreg_replace\s*(.*e' "$0" \
    | wc -l | grep -qx "5" && echo "OK." || echo "Failed: Miscellaneous signatures"
