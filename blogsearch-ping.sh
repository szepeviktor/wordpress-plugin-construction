#!/bin/bash
#
# Ping Google Blogsearch by sending your sitemap.
#

SITE_NAME="$1"
SITE_URL="$2"
SITEMAP_URL="$3"

PING_URL="http://blogsearch.google.hu/ping/RPC2"

if [ -z "$SITE_NAME" ] || [ -z "$SITE_URL" ] || [ -z "$SITEMAP_URL" ]; then
    echo "Usage: $0 SITE-NAME SITE-URL SITEMAP-URL" 1>&2
    exit 1
fi

POST_DATA="<?xml version='1.0'?>
<methodCall>
  <methodName>weblogUpdates.extendedPing</methodName>
  <params>
    <param>
      <value>${SITE_NAME}</value>
    </param>
    <param>
      <value>${SITE_URL}</value>
    </param>
    <param>
      <value>${SITEMAP_URL}</value>
    </param>
  </params>
</methodCall>
"

if ! wget -q -O- --post-data="$POST_DATA" --header="Content-Type: text/xml" "$PING_URL" \
    | grep -q '<name>flerror</name><value><boolean>0</boolean></value>'; then
    echo "ERROR during RPC call: $?" 1>&2
    exit 2
fi
