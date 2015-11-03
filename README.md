# Wordpress plugin construction

A playground where WordPress plugin development goes on.
Please select a folder in the list above to see the plugin's development.

### An article about code quality

http://engineering.quora.com/Moving-Fast-With-High-Code-Quality

### How to add images to a WordPress plugin?

- assets/banner-772x250.png
- assets/icon-128x128.png
- assets/icon-256x256.png
- assets/screenshot-1.jpg (530px + 1+1 border)

### Recommended plugins

- https://vip.wordpress.com/plugins/
- http://wpgear.org/

- Remove emoji Javascript: `classic-smilies`
- Email "From:" header: `wp-mailfrom-ii`
- SMTP settings: mu-smtp-uri/, `smtp-uri`, `danielbachhuber/mandrill-wp-mail`
- Security: wordpress-fail2ban/, `sucuri-scanner`, `custom-sucuri`
- Additional security: mu-nofollow-robot-trap/, contact-form-7-robot-trap/, `obfuscate-email`
- Redirects: `safe-redirect-manager`
- Audit: `simple-history`
- User roles: `user-role-editor`
- Comments: `disable-comments`, mu-disable-comments/
- Post connector: `post-connector`, `posts-to-posts`, `related-posts-for-wp`

### Content plugin categories

1. Forcing
    + mu-protect-plugins/
    + `force-featured-image`
    + mu-deny-giant-image-uploads/
    + `prevent-concurrent-logins`
    + `user-session-control`
1. Fixes
    + mu-shortcode-unautop/
    + `custom-post-type-permalinks`
1. UI tuning / bulk edit aid
    + Editor: `tinymce-advanced`
    + Lenghten taxonomy selector boxes, see: content-extras/nav-menu-meta-box-length.php https://core.trac.wordpress.org/ticket/32237
    + Keep category tree in post editor Category Checklist Tree `category-checklist-tree`
    + mu-strip-dashboard/
    + `wp-solarized`
    + `mark-posts`
    + https://github.com/fusioneng/Unified-Post-Types
    ```php
    add_filter( 'unified_post_types', function ( $post_types ) {
        $post_types[] = 'portfolio';
        $post_types[] = 'news';
        return $post_types;
    });
    ```
    + `simple-page-ordering`
    + `post-types-order`
    + Media URL column, see: content-extras/media-url-column.php
    + `codepress-admin-columns`
    + `featured-image-column`
    + `advanced-excerpt`
    + Advanced Image Styles `advanced-image-styles`
    + `unattach`
1. Content
    + `custom-content-shortcode`
    + `column-shortcodes`
    + `tablepress`
    + Map `wp-geo`
    + `ankyler`
1. Imaging
    + Cloudinary
    + `my-eyes-are-up-here`
1. Develop, debug, monitoring
    + `query-monitor`
    + `p3-profiler`
    + `error-log-monitor`

### Manage plugins with composer

http://wpackagist.org/

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
wp-content/sucuri/

# What other directories to look for
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
 * Trigger fail2ban on Revolution Slider upload attempt.
 *
 * @revslider/revslider_admin.php:389
 *     case "update_plugin":
 * Comment out
 *     //self::updatePlugin(self::DEFAULT_VIEW);
 */
error_log( 'Break-in attempt detected: ' . 'revslider_update_plugin' );
```
