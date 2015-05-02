#!/bin/bash
#
# Run WordPress cron via the webserver.
#
# VERSION       :0.1
# DATE          :2015-05-01
# AUTHOR        :Viktor Szépe <viktor@szepe.net>
# LICENSE       :The MIT License (MIT)
# URL           :https://github.com/szepeviktor/debian-server-tools
# BASH-VERSION  :4.2+
# LOCATION      :/usr/local/bin/wp-cron-www.sh
# DEPENDS       :apt-get install php5-cli

# Disable wp-cron in your wp-config.php
#
#     define( 'DISABLE_WP_CRON', true );

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

HTTP_USER_AGENT="Wp-cron-http/$(Get_meta) (php-cli; Linux)"

HEADERS_FIFO="$(mktemp -u)"
trap "rm -f '$HEADERS_FIFO' &> /dev/null" EXIT
mkfifo --mode=600 "$HEADERS_FIFO"

# Background HTTP request
wget -q -S -O- --tries=1 --timeout=5 --user-agent="$HTTP_USER_AGENT" \
    "$WPCRON_URL" 2> "$HEADERS_FIFO" &

# Die on error or missing headers and report non-200 responses
if head -n 1 "$HEADERS_FIFO" | grep "^  HTTP/" | grep -v "^  HTTP/1\.1 200 OK$" >&2; then
    Die 2 "Non-200 HTTP status code during ${WPCRON_URL}"
fi

wait $!
WGET_RET="$?"
if [ "$WGET_RET" != 0 ]; then
    # http://www.gnu.org/software/wget/manual/html_node/Exit-Status.html
    Die 2 "Wget exit status ${WGET_RET} during ${WPCRON_URL}"
fi

exit 0
