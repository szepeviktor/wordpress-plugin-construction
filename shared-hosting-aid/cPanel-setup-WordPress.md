# WordPress website setup on cPanel

### cPanel settings

- Contact Information ×2
- FTP Accounts
- Backup
- Subdomains
- Email Account, Quota
- Email Account Forwarders
- Email Authentication
- Spamassassin
- Web analytics
- Cron Jobs: WP-cron + `/home/USER/bin/siteprotection.sh`
- PHP Version / Options / Extensions

```
log_errors          Off
max_execution_time  65
memory_limit        512M
open_basedir        /home/USER:/opt/alt/php56/usr/share/pear:/opt/alt/php56/usr/share/php
post_max_size       32M
short_open_tag      Off
upload_max_filesize 32M
```

### Apache config

h5bp: https://github.com/h5bp/server-configs-apache/blob/master/dist/.htaccess
compression, caching, security -> shared-hosting-aid/.htaccess

debian-server-tools/webserver/apache-conf-available/wordpress-htaccess/
// Custom entry points

everything from Skeleton-site-ssl.conf

### PHP config

See /shared-hosting-aid/php-vars.php

// Upload, run and copy output of shared-hosting-aid/enable-logging.php
// Browse to /enable-logging.php?above

### WordPress maintenance

- Set up wp-config.php
- Core update
- Uninstall and update Themes, Plugins
- Install Classic Smilies plugin
- Install WP Mail From II plugin

// WordPress Fail2ban + Miniban (custom entry points exceptions)
// Copy `wordpress-fail2ban/mu-plugin/wp-fail2ban-mu.php` into `wp-content/mu-plugins/` and set `$trigger_count = 1;`


### WordPress settings

- blog_public
- admin_email

### Checks

http://www.webpagetest.org/

### Backup files and database

cPanel/Backup
