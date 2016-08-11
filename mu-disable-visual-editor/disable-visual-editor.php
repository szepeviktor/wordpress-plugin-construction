<?php
/*
Plugin Name: Disable Visual Editor (MU)
Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
Description: Disable the HTML editor for all users.
Version: 0.1.0
License: The MIT License (MIT)
Author: Viktor Szépe
Author URI: http://www.online1.hu/webdesign/
GitHub Plugin URI: https://github.com/szepeviktor/wordpress-plugin-construction
*/

/**
 * Disable the HTML editor for all users
 */
add_filter( 'get_user_option_rich_editing', '__return_false' );
