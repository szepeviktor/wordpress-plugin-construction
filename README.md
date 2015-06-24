wordpress-plugin-construction
=============================

A playground where WordPress plugin development goes on.
Please select a folder in the list above to see your plugin's development.

### How to add images to a WordPress plugin?

- assets/banner-772x250.png
- assets/icon-128x128.png
- assets/icon-256x256.png
- assets/screenshot-1.jpg (532x)

### Content plugin categories

1. bulk edit aid
    + Lenghten taxonomy selector boxes, see: content-extras/nav-menu-meta-box-length.php https://core.trac.wordpress.org/ticket/32237
    + Keep category tree in post editor Category Checklist Tree `category-checklist-tree`
1. feature
    + Advanced Image Styles `advanced-image-styles`
    + media URL column, see: content-extras/media-url-column.php
    + Admin Columns `codepress-admin-columns` e.g. post ID column
1. integration
    + Cloudinary
1. UI cleaning
    + mu-strip-dashboard/
    + https://github.com/fusioneng/Unified-Post-Types
    ```php
    add_filter( 'unified_post_types', function ( $post_types ) {
        $post_types[] = 'portfolio';
        $post_types[] = 'news';
        return $post_types;
    });
    ```

### WordPress .gitignore

```
*.log
wp-config.php
wp-content/uploads
wp-content/cache/
wp-content/upgrade/
# _get_dropins()
wp-content/advanced-cache.php
wp-content/db.php
wp-content/db-error.php
wp-content/install.php
wp-content/maintenance.php
wp-content/object-cache.php
# Plugin data directories
wp-content/w3tc-config/
wp-content/updraft/
#
#wp-content/some-other-cache/
#wp-content/uploads/some-cache/
#wp-content/themes/THEME/some-cache/
#wp-content/plugins/PLUGIN/some-cache/
#large-files-in-docroot/
#!dont/exclude.this
```

### Revolution Slider fix

```php
/*
 * Trigger fail2ban on revslider upload attempt.
 *
 * @revslider/revslider_admin.php:389
 *     case "update_plugin":
 * Comment out
 *     self::updatePlugin(self::DEFAULT_VIEW);
 */
for ( $fb = 0; $fb < 6; $fb++ ) {
    error_log( 'Malicious traffic detected: ' . 'revslider/update_plugin' );
}
```
