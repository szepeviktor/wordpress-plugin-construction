#!/bin/bash

#require(_once)?\(.*\);
#include(_once)?\(.*\);
#
#dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) \. '
#dirname( dirname( dirname( __FILE__ ) ) ) \. '
#dirname( dirname( __FILE__ ) ) \. '
#dirname( __FILE__ ) \. '
#
#ABSPATH \. WPINC \. '
#ABSPATH \. '
#ABSPATH . "
#WP_CONTENT_DIR \. '

repeat() {
    local COUNT="$1"
    local PATTERN="$2"
    local SPACES

    printf -v "SPACES" '%*s' "$COUNT"
    echo "${SPACES// /${PATTERN}}"
}

path2root(){
    # strip leading slash
    local FILE_PATH="${1#/}"
    # remove all non-slashes
    local SLASHES="${FILE_PATH//[^\/]/}"

    echo "$(repeat "${#SLASHES}" "../")"
}

dirname_1() {
    WP_PATH="'$(dirname "$FILE")"
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)dirname( \?__FILE__ \?) \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

dirname_2() {
    WP_PATH="'.."
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)dirname( \?dirname( \?__FILE__ \?) \?) \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

dirname_3() {
    WP_PATH="'../.."
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)dirname( \?dirname( \?dirname( \?__FILE__ \?) \?) \?) \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

dirname_4() {
    WP_PATH="'../../.."
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)dirname( \?dirname( \?dirname( \?dirname( \?__FILE__ \?) \?) \?) \?) \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

abspath_wpinc() {
    WP_PATH="'$(path2root "$FILE")wp-includes"
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)ABSPATH \?\. \?WPINC \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

abspath_singleq() {
    WP_PATH="'$(path2root "$FILE")"
    WP_PATH="${WP_PATH//\//\\/}"
    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)ABSPATH \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}

abspath_doubleq() {
    WP_PATH="\\\"$(path2root "$FILE")"
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)ABSPATH \?\. \?"/\1'"${WP_PATH}/g" "$FILE"
}

wpcontent_dir() {
    WP_PATH="'$(path2root "$FILE")wp-content"
    WP_PATH="${WP_PATH//\//\\/}"

    sed -i 's/\(\(include\|include_once\|require\|require_once\) \?(\? \?\)WP_CONTENT_DIR \?\. \?'"'/\1${WP_PATH}/g" "$FILE"
}


pushd wordpress/

# remove non-PHP files
find . -type f -not -name "*.php" -delete

# remove old themes
find wp-content/themes/ -mindepth 1 -maxdepth 1 -type d -not -name twentyfourteen \
    | xargs rm -rf

find . -type f -name "*.php" -printf "%P\n" \
    | while read FILE; do
        echo "${FILE} ..."

        dirname_4
        dirname_3
        dirname_2
        dirname_1

        abspath_wpinc
        abspath_singleq
        abspath_doubleq
        wpcontent_dir
    done

echo "All that left is:"
grep --color -P -rnH "(include|include_once|require|require_once)\b.*(dirname|ABSPATH|WP_CONTENT_DIR)" *

popd

echo "Run phpxref.pl"
echo "put files in the WEBROOT/subdomain"

