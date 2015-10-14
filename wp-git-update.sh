#!/bin/bash
#
# Update WordPress core under git control.
#

# Copy this to your wp-config.php
#
#     define( 'WP_AUTO_UPDATE_CORE', false );

Error() {
    echo "ERROR: #$*" >&2
    exit $1
}

# Go to WP root directory
WP_WORKING_TREE="$(git rev-parse --show-toplevel)"

if [ $? == 0 ] || [ -n "$WP_WORKING_TREE" ] || [ -d "$WP_WORKING_TREE" ]; then
    cd "$WP_WORKING_TREE"
else
    Error 1 "This not a git working tree"
fi

# Is it WordPress from GitHub?
git config --get remote.origin.url | grep -qFx "https://github.com/WordPress/WordPress.git" \
    || Error 2 "This is not WP.git"

# Detect changes (maybe auto update ran)
if [ -z "$(git status -s)" ]; then
    git reset --hard
else
    git status
    echo "Revert all:  git reset --hard"
    Error 3 "There are changes in the working-tree"
fi

# git log --decorate -1
echo "Here we were:$(git --no-pager log --pretty=format:"%d" -1)"

# Fetch new commits and tags
git fetch --prune --tags || Error 4 "Fetch failed."

LATEST_TAG="$(git tag | tail -n 1)"

if ! git checkout "$LATEST_TAG"; then
    Error 5 "Checkout failed"
fi

if ! wp --allow-root core update-db; then
    Error 6 'Database update failed!'
fi
# @TODO Handle DB update without wp-cli
