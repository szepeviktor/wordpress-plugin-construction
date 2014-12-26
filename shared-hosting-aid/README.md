### wp-config.php

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/block-bad-requests/wp-login-bad-request.inc.php

```php
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );
```

```php
// https://github.com/szepeviktor/hosting-check/blob/master/hc-query.php
ini_set( 'error_log', 'HOME/SECRETDIR/error.log' );
ini_set( 'log_errors', 1 );

//define( 'WP_DEBUG', true );
define( 'WP_DEBUG', false );

define( 'WP_MAX_MEMORY_LIMIT', '96M');
//define( 'WP_MAX_MEMORY_LIMIT', '196M');
define( 'WP_POST_REVISIONS', 10 );
define( 'WP_USE_EXT_MYSQL', false);

//define( 'WP_CACHE', true);

define( 'DISABLE_WP_CRON', true);
define( 'AUTOMATIC_UPDATER_DISABLED', true);
define( 'DISALLOW_FILE_EDIT', true);
define( 'ITSEC_FILE_CHECK_CRON', true );

define( 'ENABLE_FORCE_CHECK_UPDATE', true );

/*
// upload and session directory
ini_set( 'upload_tmp_dir', '%s/tmp' );
ini_set( 'session.save_path', '%s/session' );
// comment out after first use
mkdir( '%s/tmp', 0700 );
mkdir( '%s/session', 0700 );
*/

/*
// for different FTP/PHP UID
define( 'FS_METHOD', 'direct' );
define( 'FS_CHMOD_DIR', (0775 & ~ umask()) );
define( 'FS_CHMOD_FILE', (0664 & ~ umask()) );
*/

```

### Root files

- .htaccess
- *htaccess*
- index.html
- index.php
- favicon.ico
- robots.txt
- apple-touch-icon.png
- apple-touch-icon-precomposed.png
- browserconfig.xml
- crossdomain.xml
- labels.rdf
- sitemap.xml
- sitemap.xml.gz

https://github.com/h5bp/mobile-boilerplate/blob/master/index.html

### Block access to a directory

`.htaccess`

```apache
Deny from all
```


### MU plugins

- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-disable-updates/disable-updates.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php
