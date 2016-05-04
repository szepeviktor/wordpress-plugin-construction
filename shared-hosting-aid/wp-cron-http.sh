#!/bin/bash
#
# Run WordPress cron via the webserver.
#
# VERSION       :0.3.1
# DATE          :2015-07-08
# AUTHOR        :Viktor Szépe <viktor@szepe.net>
# LICENSE       :The MIT License (MIT)
# URL           :https://github.com/szepeviktor/debian-server-tools
# BASH-VERSION  :4.2+
# LOCATION      :/usr/local/bin/wp-cron-http.sh

# Disable wp-cron in your wp-config.php
#
#     define( 'DISABLE_WP_CRON', true );
#
# Create cron job for an existing user with email delivery
#
#     01,31 *	* * *	someuser	/usr/local/bin/wp-cron-http.sh http://SITE-URL/SUBDIR/wp-cron.php

WPCRON_URL="$1"

Die() {
    local RET="$1"

    shift
    echo -e "[wp-cron-http] $*" >&2
    exit "$RET"
}

Get_meta() {
    # defaults to self
    local FILE="${1:-$0}"
    # defaults to "VERSION"
    local META="${2:-VERSION}"
    local VALUE="$(head -n 30 "$FILE" | grep -m 1 "^# ${META}\s*:" | cut -d ':' -f 2-)"

    if [ -z "$VALUE" ]; then
        VALUE="(unknown)"
    fi
    echo "$VALUE"
}

if [ -z "$WPCRON_URL" ]; then
    Die 1 "ERROR: Please specify wp-cron URL."
fi

# Set query
WPCRON_URL="${WPCRON_URL%%\?*}"
WPCRON_URL+="?doing_wp_cron=$(date "+%s.%N")"

HTTP_USER_AGENT="Wp-cron-http/$(Get_meta) (Wget; Linux)"

HEADERS_FIFO="$(mktemp -u)"
mkfifo --mode=600 "$HEADERS_FIFO"
trap "rm -f '$HEADERS_FIFO' &> /dev/null" EXIT

# Background HTTP request
wget -q -S -O- --max-redirect=0 --tries=1 --timeout=10 --user-agent="$HTTP_USER_AGENT" \
    "$WPCRON_URL" 2> "$HEADERS_FIFO" &

# Fix WGET_RET=2
sleep 1

# Die on error or missing headers and report non-200 response
if grep -m 1 "^  HTTP/" "$HEADERS_FIFO" | grep -vFx "  HTTP/1.1 200 OK" 1>&2; then
    Die 2 "Non-200 HTTP status code during ${WPCRON_URL}"
fi
# @FIXME direct wget 2> to a file

wait "$!"
WGET_RET="$?"
if [ "$WGET_RET" != 0 ]; then
    # http://www.gnu.org/software/wget/manual/html_node/Exit-Status.html
    Die 3 "Wget exit status ${WGET_RET} during ${WPCRON_URL}"
fi

exit 0
