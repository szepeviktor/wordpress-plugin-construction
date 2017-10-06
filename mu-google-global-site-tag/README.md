# Google Analytics Global Site Tag for WordPress

### Setup

1. Copy `google-global-site-tag.php` to `/wp-content/mu-plugins/`
1. On WordPress admin go to Settings / General
1. Enter your Facebook Pixel ID, click "Save settings"

## Constants

### `GST_DISABLE`

Disables the tracking code.
Define it as `true` in `wp-config`.
By default the tracking code is enabled.

### `GST_GOOGLE_RECOMMENDATION`

Inserts the tracking code immediately after the opening `<head>` tag.
Define it as `true` in `wp-config`.
Default is before the closing `</body>` tag.

## Filters

### `gst_capability`

Disable for users with this capability. Defaults to `edit_pages`

## Examples

Custom usage in your theme.

```php
// In wp-config
define( 'GST_DISABLE', true );

// In the theme
print gst()->get_code();
```

## Remarks

On development sites the tracking code is not enabled, when `WP_ENV` is not `production`.

[Add gtag.js to your site](https://developers.google.com/analytics/devguides/collection/gtagjs/)
