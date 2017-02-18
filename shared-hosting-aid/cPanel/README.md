# WordPress website setup on cPanel

### Hawk Host

Features

1. LiteSpeed webserver with HTTP/2
1. (180 seconds long) PHP opcache
1. Memcached for WordPress object-cache
1. Fast disk
1. MariaDB version 10 database engine
1. Modern cPanel with SSH access thus you can use WP-CLI
1. [European location](https://www.hawkhost.com/our-hosting-network/amsterdam)
1. Stability of SoftLayer, an IBM company

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
- Cron Jobs: WP-cron
- Cron Jobs: `/home/USER/bin/siteprotection.sh`
- 7 × cron Jobs: `${HOME}/bin/s3cmd sync -q --no-mime-magic -e --delete-removed --delete-after "--exclude=WPCORE/*" "--exclude=SOMETHING/*" ${HOME}/public_html s3://BUCKET/0/`
- PHP Selector extensions, see `/PathInfo.php`
- PHP Selector options, see `.user.ini`
- Let's Encrypt
- SSH

### Dot files

```bash
nano .bashrc
#     umask 022
#     export TZ=UTC0
#     export LANG=en_US.UTF-8
#     export LC_ALL=en_US.UTF-8
#     export MC_SKIN=dark

# C-x C-r
nano ~/.inputrc
#     set input-meta on
#     set output-meta on
#     "\e[1~": beginning-of-line
#     "\e[4~": end-of-line
#     "\e[5~": history-search-backward
#     "\e[6~": history-search-forward

# MySQL
wp eval 'file_put_contents(".my.cnf",sprintf("[mysql]\ndefault-character-set = utf8\nuser = %s\npassword = \"%s\"\n",DB_USER,DB_PASSWORD));'
chmod -c 0600 ~/.my.cnf
```

### MC

```bash
wget https://github.com/szepeviktor/debian-server-tools/raw/master/package/mc-user-rpm.sh
bash mc-user-rpm.sh
```

### S3CMD and pip

```bash
wget -nv https://bootstrap.pypa.io/get-pip.py
python2 get-pip.py --user
rm -f get-pip.py
ln -sv ../.local/bin/pip2 bin/
pip2 install --user setuptools wheel -U

pip2 install --user s3cmd
chmod +x .local/bin/s3cmd
ln -sv ../.local/bin/s3cmd bin/
s3cmd --configure
```

Set up S3 bucket.

### WP-CLI

```bash
wget -O bin/wp https://raw.github.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x bin/wp
wget -qO- https://github.com/wp-cli/wp-cli/raw/master/utils/wp-completion.bash >> ~/.bashrc
wget https://github.com/szepeviktor/debian-server-tools/raw/master/webserver/wp-cli.yml
nano wp-cli.yml
```

### Apache configuration

See `.htaccess`

See debian-server-tools/webserver/apache-conf-available/wordpress-htaccess/

### PHP configuration check and logging

```bash
cd ~/public_html/
wget https://github.com/szepeviktor/debian-server-tools/raw/master/webserver/PathInfo.php
wget https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/shared-hosting-aid/php-vars.php
wget https://github.com/szepeviktor/wordpress-plugin-construction/raw/master/shared-hosting-aid/enable-logging.php
```

Browse to `/enable-logging.php?above`

### WordPress website

```bash
cd ~/public_html/
wp core download
wget https://github.com/szepeviktor/debian-server-tools/raw/master/webserver/wp-config.php
nano wp-config.php
wp core install --url= --title=WP --admin_user=viktor --admin_email=viktor@szepe.net --skip-email --admin_password=
wp option set blog_public "0"

# Migrate
wp db import dump.sql
wp core update && wp core update-db
```

See debian-server-tools/webserver/webserver/WordPress.md

Uninstall and update Themes, Plugins

- WordPress Fail2ban + Miniban (+custom entry points exceptions)
- Miniban https://github.com/szepeviktor/wordpress-fail2ban/tree/master/miniban

### WordPress settings

- blog_public
- admin_email

### Checks

http://www.webpagetest.org/

### Backup files and database

cPanel / Backup
