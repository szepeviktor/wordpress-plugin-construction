### Download phpMyAdmin

see: package/phpmyadmin-get-sf.sh

### Preparations

pma server export simulation: prepend to export.php `echo '<pre>'; var_export($_POST); var_export($_COOKIE); exit;`

Results are in `export-one-db.php`.

included files: append code to export.php `file_put_contents('./exp-o-pma-includes.txt', implode("\n", get_included_files()));`

Results are in `exp-o-pma-includes.txt`.

### Copy included files

`cat ../exp-o-pma-includes.txt|xargs -I {} cp -a --parents {} /var/www/server/exp-o/`

### Generate 2048 bit RSA encryption key

private key: `openssl genpkey -algorithm rsa -pkeyopt rsa_keygen_bits:2048 -out exp-o-private.key`

public key: `openssl rsa -in exp-o-private.key -pubout -out exp-o-public.pem`

### Setup configuration file in `exp-o-config.php`

- Initialization Vector `pwgen 16 1`
- Public key location
- HTTP secret key `pwgen 30 1`
- wp-config location

## How to backup

- Save Initialization Vector
- Save HTTP headers `wget -q -S --content-disposition --header="X-Secret-Key: <SECRET-KEY>" "https://<DOMAIN-AND-PATH>/export-one-db.php" 2> http-headers.log`
- Keep private key file **separate** and secure

## Decryption

- `./exp-o-decrypt.php <PASSWORD-FROM-HTTP-RESPONSE> <IV> <PRIVATE-KEY-FILE>`
- Execute the provided command line `openssl enc ...`
