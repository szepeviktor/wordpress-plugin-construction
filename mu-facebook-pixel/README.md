# Facebook Pixel for WordPress

### Setup

1. Copy `facebook-pixel.php` to `/wp-content/mu-plugins/`
1. On WordPress admin go to Settings / General
1. Enter your Facebook Pixel ID, click "Save settings"

## Constants

### `FBP_DISABLE`

Disables the tracking code.
Define it as `true` in `wp-config`.
By default the tracking code is enabled.

### `FBP_FACEBOOK_RECOMMENDATION`

Inserts the tracking code before the ending `</head>` tag.
Define it as `true` in `wp-config`.
Default is before the closing `</body>` tag.

## Filters

### `fbp_capability`

Disable for users with this capability. Defaults to `edit_pages`

### `fbp_extra_javascript`

Track custom event instead of page view. Defaults to `fbq('track', 'PageView');`

## Examples

Custom usage in your theme.

```php
// In wp-config
define( 'FBP_DISABLE', true );

// In the theme
print fbp()->get_code();
```

Adding extra JavaScript.

```php
// Analytics.js Field Reference
// https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference
add_filter( 'fbp_extra_javascript', function ( $js ) {

    $js = sprintf( "fbq('track', 'Purchase', {currency: 'EUR', value: %.2f});", $cart_total );

    return $js;
});
```

Adding a Dynamic Event.

```js
( typeof fbq === 'function' ) && fbq('track', 'Lead', {
    content_name: 'Main Contact Form submit',
    referrer: document.referrer,
    userAgent: navigator.userAgent,
    language: navigator.language
});
```

## Remarks

On development sites the tracking code is not enabled, when `WP_ENV` is not `production`.

[API Reference](https://developers.facebook.com/docs/facebook-pixel/api-reference)
