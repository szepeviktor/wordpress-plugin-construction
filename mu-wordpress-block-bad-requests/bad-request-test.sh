#!/bin/bash
#
# Test all fail2ban triggers in O1_Bad_Request.
# Set all variables below. Stop the webserver and use `nc -l -p 80` to grab values.
# Test then COMMENT out "local access" check in O1_Bad_Request.
#
# VERSION       :0.1
# DATE          :2014-08-16
# AUTHOR        :Viktor Szépe <viktor@szepe.net>
# LICENSE       :The MIT License (MIT)
# URL           :https://github.com/szepeviktor/wordpress-plugin-construction
# BASH-VERSION  :4.2+
# DEPENDS       :apt-get install netcat-traditional


HOST="subdir.wp"
PORT="80"
REQUEST="/sb/wp-login.php"
WP_ADMIN="/sb/wp-admin/"
USERNAME="viktor"
USERPASS="v12345"
PROTOCOL="HTTP/1.1"
UA="Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:24.7) Gecko/20140802 Firefox/24.7 PaleMoon/24.7.1"
ACCEPT="text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
ACCEPT_LANG="hu,en-us;q=0.7,en;q=0.3"
ACCENT_ENC="gzip, deflate"
REFERER="http://${HOST}${REQUEST}"
COOKIE="wordpress_test_cookie=WP+Cookie+check"
CONNECTION="keep-alive"
CONTENT_TYPE="application/x-www-form-urlencoded"
CONTENT="log=${USERNAME}&pwd=${USERPASS}&wp-submit=Bejelentkez%C3%A9s&redirect_to=http%3A%2F%2F${HOST}%2Fsb%2Fwp-admin%2F&testcookie=1"
CONTENT_LENGTH="${#CONTENT}"

CR=$'\r'
# seconds to wait for the response from WordPress
RESPONSE_WAIT="1"
RESPONSE_TEMPLATE="^HTTP/1\.1 %s${CR}\$"
RESPONSE_COOKIE="^Set-Cookie: wordpress_.*; path=.*; httponly${CR}\$"
RESPONSE_LOC="^Location: http://${HOST}${WP_ADMIN}${CR}\$"

display_file() {
    local FILE="$1"

    if [ -s "$FILE" ]; then
        head -n 12 "$FILE"
    else
        echo "[empty file]"
    fi
}

check_response() {
    local FILE="$1"
    local HTTP_STATUS="$2"

    printf -v RESPONSE "$RESPONSE_TEMPLATE" "$HTTP_STATUS"

    #DEBUG echo -n "if ! grep -q $RESPONSE $FILE" | hexdump -C

    if ! grep -q "$RESPONSE" "$FILE"; then
        echo "invalid HTTP status code ($(display_file "$FILE"))" >&2
        return 1
    fi

    # return OK on other responses
    [ "$HTTP_STATUS" = "302 Found" ] || return 0

    if ! grep -q "$RESPONSE_LOC" "$FILE"; then
        echo "missing redirect to WP dashboard" >&2
        return 1
    fi

    if ! grep -q "$RESPONSE_COOKIE" "$FILE"; then
        echo "WP auth cookie not found" >&2
        return 1
    fi

    return 0
}

wp_login() {
    local NAME="$1"
    local HTTP_STATUS="$2"
    local FILE="$(tempfile)"
    local PID

    #( sleep $RESPONSE_WAIT; killall -9 nc &> /dev/null; ) &

    nc -w $RESPONSE_WAIT $HOST $PORT > "$FILE"

    if check_response "$FILE" "$HTTP_STATUS"; then
        echo "OK: $NAME"
    else
        echo "FAILED: $NAME"
    fi

    echo

    rm "$FILE"
}


cat <<LOGIN | wp_login "login" "302 Found"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "author sniffing" "403 Forbidden"
GET /?author=2 $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-DEncoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION

LOGIN


cat <<LOGIN | wp_login "only POST requests to wp-login" "404 Not Found"
POST /sb/wp-trackback.php $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-DEncoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "banned usernames" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: 116

log=admin&pwd=$USERPASS&wp-submit=Bejelentkez%C3%A9s&redirect_to=http%3A%2F%2Fsubdir.wp%2Fsb%2Fwp-admin%2F&testcookie=1
LOGIN


cat <<LOGIN | wp_login "attackers use usernames with 'TwoCapitals'" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: 119

log=UserName&pwd=$USERPASS&wp-submit=Bejelentkez%C3%A9s&redirect_to=http%3A%2F%2Fsubdir.wp%2Fsb%2Fwp-admin%2F&testcookie=1
LOGIN


cat <<LOGIN | wp_login "accept header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: application
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "accept-language header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: a
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "content-type header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: application/x-www-form
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "content-length header" "400 Bad Request"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE

$CONTENT
LOGIN


cat <<LOGIN | wp_login "referer header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: http://www.non.host${REQUEST}
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "don't ban password protected posts (should FAIL)" "302 Found"
POST $REQUEST?action=postpass $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: 39

post_password=1&Submit=K%C3%BCld%C3%A9s
LOGIN


cat <<LOGIN | wp_login "protocol version" "403 Forbidden"
POST $REQUEST HTTP/1.0
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "connection header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: close
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "accept-encoding header" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: deflate
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "cookie" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: $UA
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: not-ok=value
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "empty user agent" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "botnets" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: xy crawler
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN


cat <<LOGIN | wp_login "modern browsers" "403 Forbidden"
POST $REQUEST $PROTOCOL
Host: $HOST
User-Agent: Mozilla/4.0 (Windows NT 6.1; Win64; x64; rv:24.7) Gecko/20140802 Firefox/0.7
Accept: $ACCEPT
Accept-Language: $ACCEPT_LANG
Accept-Encoding: $ACCENT_ENC
Referer: $REFERER
Cookie: $COOKIE
Connection: $CONNECTION
Content-Type: $CONTENT_TYPE
Content-Length: $CONTENT_LENGTH

$CONTENT
LOGIN

