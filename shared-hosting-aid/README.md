### FTP/SFTP access

```bash
SSLOFF="set ftp:ssl-allow off;"

# lftp -e "$SSLOFF cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
lftp -e "cd" -u 'FTP-USER,FTP_PASS' FTP_HOST.
```

### Move/clone site with lftp

```bash
#!/usr/bin/lftp -f
#open ftp://domain.tld
#cd website/html/
mkdir sr; cd sr
!wget -nv -N https://github.com/interconnectit/Search-Replace-DB/raw/master/index.php
!wget -nv -N https://github.com/interconnectit/Search-Replace-DB/raw/master/srdb.class.php
put index.php; put srdb.class.php
#
# mrm *; cd ..; rmdir sr
```

#### Search & replace items

`wp search-replace --precise --recurse-objects --all-tables-with-prefix`

1. http://domain.tld or https (no trailing slash)
1. /var/www/path/to/site (no trailing slash)
1. email@address.es
1. domain.tld

Manual replace: constants in wp-config.


### Webserver settings

https://github.com/szepeviktor/hosting-check

- keep-alive
- mime-type
- content-compression
- content-cache

https://github.com/h5bp/html5-boilerplate/blob/master/.htaccess

https://redbot.org/

#### PHP settings

see: shared-hosting-aid/php-vars.php

#### Default email from address

see: shared-hosting-aid/php-mail-sender.php

- Set sender or forward as necessary.
- Set usual addresses: info@, postmaster@, abuse@
- Set up your account: webmaster@DOMAIN

### Security plugin

- Sucuri plugin + sucuri-cleanup
- (iThemes Security plugin)

### Plugins to protect

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php

### wp-config.php

#### Change salt

- Sucuri plugin
- https://api.wordpress.org/secret-key/1.1/salt/

#### Block Bad Requests

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/block-bad-requests/wp-login-bad-request.inc.php

`wp-config.php`

```php
define( 'O1_BAD_REQUEST_COUNT', 1 );
//define( 'O1_BAD_REQUEST_ALLOW_CONNECTION_CLOSE', true );
require_once( dirname( __FILE__ ) . '/wp-login-bad-request.inc.php' );

// see: shared-hosting-aid/enable-logging.php
//ini_set( 'error_log', '/path/to/error.log' );
//ini_set( 'log_errors', 1 );

//define( 'WP_CONTENT_DIR', '<DOCUMENT-ROOT>/site' );
//define( 'WP_CONTENT_URL', '<DOMAIN>/static' );
// siteurl .= /site , search-replace: /wp-includes/ -> /site/wp-includes/ , /wp-content/ -> /static/

// Live debugging
// see: /wp-config-live-debugger/
define( 'WP_DEBUG', false );

define( 'WP_MEMORY_LIMIT', '96M' );
//define( 'WP_MAX_MEMORY_LIMIT', '384M' );
define( 'WP_POST_REVISIONS', 10 );
define( 'WP_USE_EXT_MYSQL', false );
define( 'DISALLOW_FILE_EDIT', true );

//define( 'WP_CACHE', true);

// web cron, see: shared-hosting-aid/wp-cron-http.sh
// CLI cron, see: debian-server-tools:/webserver/wp-cron-cli.sh
// simple CLI cron:  /usr/bin/php <ABSPATH>/wp-cron.php  # stdout, stderr to cron email
define( 'DISABLE_WP_CRON', true );
define( 'AUTOMATIC_UPDATER_DISABLED', true );
define( 'ENABLE_FORCE_CHECK_UPDATE', true );

//define( 'ITSEC_FILE_CHECK_CRON', true );
//define( 'ITSEC_BACKUP_CRON', true );

/*
// Upload and session directory.
ini_set( 'upload_tmp_dir', '<HOME>/tmp' );
ini_set( 'session.save_path', '<HOME>/session' );

// Test - Comment out after first use!
mkdir( '<HOME>/tmp', 0700 );
mkdir( '<HOME>/session', 0700 );
*/

/*
// For different FTP/PHP UID.
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
- robots.txt
- sitemap.xml
- sitemap.xml.gz
- crossdomain.xml
- labels.rdf

https://github.com/h5bp/mobile-boilerplate/blob/master/index.html

- favicon.ico
- apple-touch-icon.png
- apple-touch-icon-precomposed.png
- apple-touch-icon*.png
- browserconfig.xml

http://realfavicongenerator.net/
http://realfavicongenerator.net/favicon_checker

Don't index files for robots `.htaccess`.

```apache
# Don't index files for robots
<FilesMatch "^(robots\.txt|sitemap\.xml|sitemap\.xml\.gz)$">
    Header append X-Robots-Tag "noindex"
</FilesMatch>
```

### PHP security check

Enable access in `.htaccess`.

```apache
SetEnv PCC_ALLOW_IP 1.2.3.4
```

https://github.com/sektioneins/pcc/raw/master/phpconfigcheck.php

### WordPress security check

- Gauntlet Security plugin

### File change notification

#### Sucuri plugin

#### Tripwire

```bash
git clone --recursive https://github.com/szepeviktor/Tripwire.git
cd Tripwire/
cp tripwire_config.sample.ini tripwire_config.ini
editor tripwire_config.ini
```

Tripwire access protection `.htaccess`.

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{REMOTE_ADDR} !=<MANAGEMENT-SERVER-IP-ADDRESS> [OR]
  RewriteCond %{REQUEST_URI} !=/<SECRET_DIR>/tripwire.php
  RewriteRule ^ - [F]
</IfModule>
```

### Block access to a directory

Deny all traffic `.htaccess`.

```apache
# Apache < 2.3
<IfModule !mod_authz_core.c>
  Order allow,deny
  Deny from all
  Satisfy All
</IfModule>
# Apache ≥ 2.3
<IfModule mod_authz_core.c>
  Require all denied
</IfModule>
```

### MySQL

- ALTER table engine
- //TODO

### List WordPress plugin names and paths

Go to the Plugins page.

```js
// plugin_names
jQuery('#wpbody .plugins .plugin-title strong').each(function (){console.log(jQuery(this).text());});

// plugin_slugs from "Deactivate" links
var plungins=[].slice.call(document.querySelectorAll('#wpbody .plugins .plugin-title .deactivate a'));
plungins.forEach(function(p){console.log(decodeURIComponent(p.search.split('&')[1].split('=')[1]));});
```

### MU plugins

- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-protect-plugins/protect-plugins.php
- https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-disable-updates/disable-updates.php

### SMTP

Use reliable smart host with STARTTLS for email sending.

https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/mu-smtp-uri/smtp-uri.php

### Monitoring

....... üzemeltetés TODO
....... -----------
.......
....... 1. ügyfeleknek szolg leírása en/hu
....... 2. áttekintés/ütemezés magamnak
....... 3. setup with snippets and links
....... 4. routine: pseudo script for copy&pasting

DNS checks: NS, A, MX, TXT(spf)

```
# <SITE-NAME> - Static file check
1 *  * * *  nobody  /usr/bin/wget -qO- <SITE.URL>/license.txt|grep -qF "GNU GENERAL PUBLIC LICENSE"
# <SITE-NAME> - PHP version and MySQL version check
1 *  * * *  nobody  /usr/bin/wget -qO- <SITE.URL>/ping.php|grep -qFx "<MD5-SUM>"
```

```php
<?php
$management_server_ip = 'MANAGEMENT_SERVER_IP';

if ( $management_server_ip !== @$_SERVER['REMOTE_ADDR'] ) {
    error_log( 'Malicious traffic detected: ping_extraneous_access '
        . addslashes( @$_SERVER['REQUEST_URI'] )
    );
    ob_get_level() && ob_end_clean();
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.0 403 Forbidden' );
    exit();
}
$wpload_path = dirname( __FILE__ ) . '/wp-load.php';
define( 'WP_USE_THEMES', false );
require_once( $wpload_path );
global $wpdb;
$mysql_version_query = "SHOW VARIABLES LIKE 'version'";
$pong = phpversion() . '|' . $wpdb->get_var( $mysql_version_query, 1 );
//exit( $pong );
exit( md5( $pong ) );
```

- wget -qO- <FRONT-PAGE>|grep -q '<h1>Title string'                  @FIXME one request only
- wget -qO- <FRONT-PAGE>|grep -qEi 'mysql|php|error|notice|warning|Account.*Suspend' @FIXME one request only
- pingdom  https://www.pingdom.com/free/
- RBL blacklists  https://www.rblmon.com/
- can-send-email @daily  use smarthost, whitelist on the smarthost, wget can-send-email.php,
wait 5 minutes, check mailbox for message with this subject:

"Subject: [admin] can-send-email from HOSTNAME"

```php
<?php

$to      = "viktor@szepe.net";

$headers = "X-Mailer: PHP/" . phpversion();
$subject = "[admin] can-send-email from {$_SERVER['SERVER_NAME']}";
// http://www.randomtext.me/download/txt/gibberish/p-5/20-35
$message = '
Much bowed when mammoth for lusciously lost a dear whooped some ouch
insufferably one indefatigably contemplated manifestly therefore much
mongoose and llama far feeble a cocky.

Robin the whistled scorpion mongoose fleetly past together toucan compulsively
coarsely inadvertent far hence within when up prissily amicable one and since
gawked jollily rude.

Met patiently excluding because and far sleazily sufficiently hyena enormously
that goodness much hawk mastodon walking this this whale ouch shed kookaburra
sleekly that one the affably.

Alarmingly much this the inoffensive in more much sobbed aboard reined that
labrador ordered much less jeez gibbered checked a wove selflessly goodness
this adjusted honey flustered a that turtle unavoidable hello messily.

Cringed apart complete bat knitted impulsively domestic behind jokingly a far
jeepers folded blubbered wildebeest lighthearted much exultingly yikes yawned
well winced swept far slowly decorously.
';
// @TODO rewrite mu-smtp-uri/smtp-uri.php for phpmailer
$mail = mail( $to, $subject, $message, $headers );
if ( true !== $mail )
    print "mail() returned: " . var_export( $mail, true );
exit;
```

- see: shared-hosting-aid/remote-log-watch.sh @*/30
- FIXME munin-plugin: log size in lines
- FIXME remote-rotate error.log
- opcache/apc/memcache control panels @weekly
- domain name expiry @monthly
- Safebrowsing, Sucuri, Virustotal check @daily
- SEO Panel @weekly
- Analytics @weekly
- Google WMT @weekly
- PageSpeed, webpagetest.org @weekly
