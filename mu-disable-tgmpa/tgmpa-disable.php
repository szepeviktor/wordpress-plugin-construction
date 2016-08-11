<?php
/*
Plugin Name: Disable TGM Plugin Activation (MU)
Version: 0.1.0
Description: https://github.com/TGMPA/TGM-Plugin-Activation
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
License: The MIT License (MIT)
Author: Viktor Szépe
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction/tree/master/mu-disable-tgmpa
*/

add_filter( 'tgmpa_load', '__return_false', 2**32-1 );
