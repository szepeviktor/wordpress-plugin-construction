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
- http://www.shutterstock.com/cat.mhtml?&searchterm=Flat%20modern%20design%20with%20shadow

## Recommended plugins

- https://vip.wordpress.com/plugins/
- http://wpgear.org/

- Bcrypt hashed passwords: https://github.com/roots/wp-password-bcrypt `password-bcrypt`
- Remove emoji Javascript: `classic-smilies`
- Email "From:" header: `wp-mailfrom-ii`
- SMTP settings: `smtp-uri`, `danielbachhuber/mandrill-wp-mail`
- Security: wordpress-fail2ban/, `sucuri-scanner`, `custom-sucuri`
- Additional security: mu-nofollow-robot-trap/, contact-form-7-robot-trap/, `obfuscate-email`
- Redirects: `safe-redirect-manager`
- Audit: `simple-history`
- User roles: `user-role-editor`
- Comments: `disable-comments`, mu-disable-comments/
- Post connector: `post-connector`, `posts-to-posts`, `related-posts-for-wp`
- Multilanguage: `polylang`

### Data structure plugin categories

- CPT (Custom port type)
- Custom taxonomy
- Custom post meta
- Custom taxonomy meta
- Custom user meta
- Plugin option page
- Theme options page
- Shortcodes
- Widgets
- Widget display conditions `widget-context`
- Search custom contents

### Content plugin categories

1. Forcing
    + mu-protect-plugins/
    + `force-featured-image`
    + mu-deny-giant-image-uploads/
    + `user-session-control`
    + `prevent-concurrent-logins`
1. Fixes
    + mu-shortcode-unautop/
    + `custom-post-type-permalinks`
1. UI tuning / bulk edit aid
    + Editor: `tinymce-advanced`
    + Lenghten taxonomy selector boxes, see: content-extras/nav-menu-meta-box-length.php https://core.trac.wordpress.org/ticket/32237
    + Keep category tree in post editor Category Checklist Tree `category-checklist-tree`
    + mu-cleanup-admin/
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

## Manage WordPress installation with git

1. Core as submodule at `/company/` with URL `https://github.com/WordPress/WordPress.git`
1. Theme as submodule with URL `file:///home/user/website/theme.git`
1. WP.org plugins are gitignore-d.
1. Non-WP.org plugins as submodules with URL `file:///home/user/website/plugin.git`

### WordPress .gitignore

```
*.log
wp-config.php
wp-content/uploads/
wp-content/cache/
wp-content/upgrade/
# From _get_dropins()
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

# What other directories to look for?
#wp-content/some-other-cache/
#wp-content/uploads/some-cache/
#wp-content/themes/THEME/some-cache/
#wp-content/plugins/PLUGIN/some-cache/
#large-files-in-docroot/

#!dont/exclude.this
```

## Manage plugins with composer

http://wpackagist.org/

