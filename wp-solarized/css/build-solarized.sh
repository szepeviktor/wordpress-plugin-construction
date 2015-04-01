#!/bin/bash

# depends: gem install sass
# depends: npm install clean-css

HERE="$PWD"

# go to wp-admin
pushd ../../../../wp-admin/css/colors/blue

# build CSS
sass --no-cache --sourcemap=none "${HERE}/colors.scss" "${HERE}/colors.css"
# minify CSS
cleancss "${HERE}/colors.css" -o "${HERE}/colors.min.css"

# same files for RTL languages
cp "${HERE}/colors.min.css" "${HERE}/colors-rtl.min.css"

popd
