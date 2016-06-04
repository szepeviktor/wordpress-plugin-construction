#!/bin/bash
#
# Download latest WordPress release.
#

rm -rf ./wordpress
wget -O- "https://wordpress.org/latest.tar.gz" | tar -xz && echo "WordPress untar OK."
