# How to check a WordPress theme

### Theme errors

- Search `http://themecheck.org/`, upload
- `https://github.com/Otto42/theme-check` plugin
- Short opentags `<?=`
- `grep -E "\brequire|include.*wp-"`
- Generate CSS, JS files `grep -E "enqueue*.php"`
- Send email `grep -E "(wp_)?mail\("`
- Inclusion of `wp-load.php` and `wp-config.php`
- Non-HTTP/200 requests
- Home call, extranal URL-s: grep URL-s
- `define( 'WP_DEBUG', true );` PHP errors, WP deprecated
- Propiertary install/update (e.g. comment out TGM-Plugin-Activation)
- Always require admin code `whats-running`

### Maintenance

- Put under git version control
- Make patch (list of patches) for next update
- Report errors to author

### Update

