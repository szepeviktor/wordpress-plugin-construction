#!/bin/bash
#
# Update WordPress core under git control with local git-dir.
#
# VERSION       :0.2.0
# DATE          :2015-12-12
# AUTHOR        :Viktor Szépe <viktor@szepe.net>
# URL           :https://github.com/szepeviktor/debian-server-tools
# LICENSE       :The MIT License (MIT)
# BASH-VERSION  :4.2+
# DEPENDS       :apt-get install git
# LOCATION      :/usr/local/bin/wp-git-update.sh

# Copy this to your wp-config.php
#
#     define( 'WP_AUTO_UPDATE_CORE', false );

Error() {
    echo "ERROR: #$*" 1>&2
    exit "$1"
}

# Go to WP root directory
WP_WORKING_TREE="$(git rev-parse --show-toplevel)"
if [ $? == 0 ] && [ -n "$WP_WORKING_TREE" ] && [ -d "$WP_WORKING_TREE" ]; then
    cd "$WP_WORKING_TREE"
else
    Error 1 "This not a git working-tree ($(pwd))"
fi

# Is it WordPress from GitHub?
git config --get remote.origin.url | grep -qFx "https://github.com/WordPress/WordPress.git" \
    || Error 2 "This is not WordPress/WordPress"

# Detect changes (maybe auto update ran)
if [ -z "$(git status -s)" ]; then
    echo "Working-tree is clean, resetting"
    git reset --hard
else
    git status
    echo "Revert all:  git reset --hard"
    Error 3 "There are changes in the working-tree"
fi

# git log --decorate -1
echo "Here we were: $(git --no-pager log --pretty=format:"%d" -1)"

# Fetch new commits and tags
git fetch --prune --tags || Error 4 "Fetch failed"

LATEST_TAG="$(git tag | tail -n 1)"

echo "Upgrading to ${LATEST_TAG}"
if ! git checkout "$LATEST_TAG"; then
    Error 5 "Checkout failed"
fi

# Upgrade database
cat << EOF | /usr/bin/php || Error 6 "Database upgrade failed"
<?php // wp --allow-root core update-db

define( 'WP_INSTALLING', true );
require 'wp-load.php';
// For wp_guess_url()
define( 'WP_SITEURL', get_option( 'siteurl' ) );
require_once 'wp-admin/includes/upgrade.php';

wp_upgrade();
delete_site_transient( 'update_core' );
print( "WordPress database upgraded successfully.\n" );
EOF
