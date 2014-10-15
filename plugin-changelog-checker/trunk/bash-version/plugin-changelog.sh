#!/bin/bash
#
# WordPress Plugin changelog checker
# Author: Viktor Szépe
# Author URI: http://www.online1.hu/webdesign/
# GitHub URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/plugin-changelog-checker/trunk
#

ALERT="<YOUR@E-MAIL.ADDR>"
PLUGIN_SLUG="<WP-PLUGIN-SLUG>"

###############################################################
PAGE_URL="https://wordpress.org/plugins/${PLUGIN_SLUG}/changelog/"
SVN_URL="plugins.svn.wordpress.org%2F%2F${PLUGIN_SLUG}%2Freadme.txt"
TOP_LINES="20"
VALIDATOR_URL="https://wordpress.org/plugins/about/validator/"

First_item() {
    grep -m 1 '<h4>' | sed 's/<[^>]\+>/ /g'
}

# get the Changelog page
PAGE="$(wget -q -O- "$PAGE_URL")"
# parse readme.txt from trunk through Readme Validator
SVN="$(wget -q -O- --post-data="url=1&readme_url=${SVN_URL}" "$VALIDATOR_URL")"

PAGE_TOP="$(grep -A ${TOP_LINES} '<div class="block-content">' <<< "$PAGE")"
PAGE_TOP_STRIP="${PAGE_TOP#*<h4>}"

SVN_TOP="$(grep -A ${TOP_LINES} '<h3>Changelog</h3>' <<< "$SVN")"
SVN_TOP_STRIP="${SVN_TOP#*<h4>}"

if ! [ "$PAGE_TOP_STRIP" == "$SVN_TOP_STRIP" ]; then
    PAGE1="$(First_item <<< "$PAGE_TOP")"
    SVN1="$(First_item <<< "$SVN_TOP")"
    echo -e "SVN: ${SVN1}\nChangelog page:${PAGE1}" | mailx -s "[${PLUGIN_SLUG}] Changelog mismatch" "$ALERT"
fi
