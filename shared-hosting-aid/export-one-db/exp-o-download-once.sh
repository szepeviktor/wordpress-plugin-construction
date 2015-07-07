#!/bin/bash
#
# Backup a remote database once.
#

# Usage
#
# Generate and upload export-one-db project.
# Set up these variables.

ID=""
SITEURL=""
SUBDIR="exp-o-once"
SECRET=""
IV=""
UA="Exp-o-once/1.0"
URL="${SITEURL}${SUBDIR}/export-one-db.php"

# Not configured
if [ -z "$SITEURL" ]; then
    echo "Please set up variables: SITEURL, SUBDIR, ID in the header." >&2
    exit 10
fi

# Download database dump
if ! wget -q -S --user-agent="$UA" \
    --header="X-Secret-Key: ${SECRET}" -O "${ID}.sql.gz.enc" "$URL" 2> "${ID}.headers"; then
    echo "Error during database backup of ${ID}." >&2
    exit 1
fi

# Check dump and header files
if ! [ -s "${ID}.headers" ] || ! [ -s "${ID}.sql.gz.enc" ]; then
    echo "Export failed ${ID}." >&2
    exit 2
fi

# Get password
PASSWORD="$(grep -m1 "^  X-Password:" "${ID}.headers"|cut -d" " -f4-)"
if [ -z "$PASSWORD" ]; then
    echo "No password found in response ${ID}." >&2
    exit 3
fi

# Decrypt dump
if ! OPENSSL_DECRYPT="$(./exp-o-decrypt.php "$PASSWORD" "$IV" ./exp-o-private.key)"; then
    echo "Password retrieval failed ${ID}." >&2
    exit 4
fi
if ! ${OPENSSL_DECRYPT} ${ID}.sql.gz.enc | gzip -d > "${ID}.sql"; then
    echo "Dump decryption failed ${ID}." >&2
    exit 5
fi
rm "${ID}.headers" "${ID}.sql.gz.enc"
