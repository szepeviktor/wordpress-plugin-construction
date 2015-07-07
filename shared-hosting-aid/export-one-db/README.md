### Use the configuration utility!

MySQL **minumum** version: `5.5`.

`exp-o-util.sh`

### Download phpMyAdmin

See: package/phpmyadmin-get-sf.sh

### Preparations

For pma server export simulation prepend this to export.php
`echo '<pre>'; var_export($_POST); var_export($_COOKIE); exit;`

Replace cookie authentication class with "config" authentication class.

See results in `export-one-db.php`.

To get the list of included files append this to export.php
`file_put_contents('./exp-o-pma-includes.txt', implode("\n", get_included_files()));`

See results in `exp-o-pma-includes.txt`.

### Copy included files from pma

`cat ../exp-o-pma-includes.txt | xargs -I {} cp -a --parents {} ../`

### Generate 2048 bit RSA encryption key

Private key:
`openssl genpkey -algorithm rsa -pkeyopt rsa_keygen_bits:2048 -out exp-o-private.key`

Public key:
`openssl rsa -in exp-o-private.key -pubout -out exp-o-public.pem`

### Set up configuration file

- Initialization Vector `pwgen 16 1`
- Public key location
- HTTP secret key `pwgen 30 1`
- wp-config location

### Remote access

Change IP address in `.htaccess`.

### What files to upload

- .htaccess / @TODO Set nginx config.
- pma files from exp-o-pma-includes.txt
- config.inc.php
- exp-o-config.php
- exp-o-public.pem
- export-one-db.php

## How to backup

- Save Initialization Vector
- Save HTTP headers
  `wget -q -S --content-disposition --user-agent="<UA>" --header="X-Secret-Key: <SECRET-KEY>" "https://<DOMAIN-AND-PATH>/export-one-db.php" 2> http-headers.log`
- Keep private key file **apart** and secure

## Decryption

- `./exp-o-decrypt.php <PASSWORD-FROM-HTTP-RESPONSE> <IV> <PRIVATE-KEY-FILE>`
- Execute the provided command line `openssl enc ...` plus `<ENCRYPTED-DUMP> | gzip -d > <ORIGINAL-DUMP>`
