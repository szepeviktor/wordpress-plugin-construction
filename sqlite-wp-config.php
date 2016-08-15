<?php
/*
    wp-config file for SQLite Integration

    Convert MySQL - mysql2sqlite.sh: https://gist.github.com/esperlu/943776

    Administer: https://bitbucket.org/phpliteadmin/public

    Installation steps

    wget -nv -O- https://wordpress.org/latest.tar.gz | tar -xz
    cd wordpress/wp-content/plugins/
    wget -nv https://downloads.wordpress.org/plugin/sqlite-integration.zip
    unzip sqlite-integration.zip
    cp sqlite-integration/db.php ../
*/

define( 'USE_MYSQL', false );
define( 'DB_NAME', 'sqlite' );
define( 'DB_CHARSET', 'utf8' );
$table_prefix = 'sql3_';

//define( 'USE_MYSQL', true );
//define( 'DB_DIR', '/ABS/PATH/wp-content/database/' );
//define( 'DB_FILE', '.ht.sqlite' );

// Dummy data for plugins
define( 'DB_USER', 'sqlite' );
define( 'DB_PASSWORD', 'sqlite' );
define( 'DB_HOST', 'sqlite' );
define( 'DB_COLLATE', '' );

/*
    Salts

    Alt + U (mcedit/Paste output of...)
    wget -qO- https://api.wordpress.org/secret-key/1.1/salt/
*/

/*
    Webserver

    apt-get install -y php5-cli
    php -S
*/


/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/wordpress/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
