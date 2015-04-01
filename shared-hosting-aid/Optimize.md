# Website optimization

## First steps

- Backup theme and plugins.
- PageSpeed score screenshots.
- Webpage test report link http://www.webpagetest.org/

## Detect problems

- Page generation time `time wget -S -O/dev/null http://site.url/`
- Theme discovery https://github.com/barrykooij/what-the-file
- Non-200 requests: 404, 30x, 5xx
- Webserver settings https://github.com/szepeviktor/hosting-check
- Progressive JPEG images, compress PNG images (export JPEG 100% or convert from PNG `convert orig.png -quality 100 100.jpg`)
- Standard HTML http://validator.w3.org/
- MinQueue plugin frontend list.
- Frontend Debugger plugin.
- Create local copies of remote scripts and styles.
- Clean up GET parameters (.css @import, wp_enqueue_*).
- Minit supports WordPress generated resources (*.css.php, *.js.php).
- Relocate scripts after `wp_print_footer_scripts`:

```php
add_action( 'wp_print_footer_scripts', function () {
    print '<!-- NO PRINTS AFTER wp_print_footer_scripts -->';
}, 0 );
```

## Fix webserver settings

https://github.com/h5bp/server-configs

## Fix resource loading


### Creating dummy handles for inline scripts and styles.

Create `css/dummy.css`, `js/dummy.js` in (child)theme directory.

```php
add_action( 'wp_enqueue_scripts', function () {
    $handle = 'dummy-style';
    wp_enqueue_style( $handle, get_stylesheet_directory_uri() . '/js/dummy.css', array(), '1.0', 'all' );
}, 10 );

add_action( 'wp_enqueue_scripts', function () {
    $handle = 'dummy-script';
    wp_enqueue_script( $handle, get_stylesheet_directory_uri() . '/js/dummy.js', array(), '1.0', true );
}, 10 );
```

### Javascript incidents

#### Directly printed `<script>` element with external script

```php
// Do in-page printed scripts need add_action()?
// @FIXRES
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer_true );
});

```

#### Directly printed `<script>` element with inline script

*No inline Javascript in WordPress.*

```php
/**
 * Introduce this script to WordPress as a localized script.
 *
 * This will be placed BEFORE the $footer_handle script in the footer.
 * FIXME Will it be printed in the footer with a header script's handle?
 *
 * @FIXRES
 */
function fixres_callback_01( $buffer ) {
    global $wp_scripts;
    $name = 'NAME-inline-script';
    $footer_handle = 'HANDLE';
    //$buffer = str_replace( '<script type="text/javascript">', '', $buffer );
    //$buffer = str_replace( '</script>', '', $buffer );
    $fix_res_banner = "/* RELOCATED {$name} */\n";
    $fix_res_data = $wp_scripts->get_data( $footer_handle, 'data' )
        . $fix_res_banner . $buffer;
    $wp_scripts->add_data( $footer_handle, 'after', $fix_res_data );
    return "<!-- FIXRES {$name} optimized -->\n";
}

// Wrap inline script printing
// @FIXRES
ob_start( 'fixres_callback_01' );
    // echo $inline_script;
ob_end_flush();
// @FIXRES - END
```

#### Directly printed `<script>` element with data only

```php
// @FIXRES
wp_localize_script( $footer_handle, $js_var_name, $data );
```


### Stylesheet incidents

#### Directly printed `<link>` element with external stylesheet

```php
//FIXME Does it work in the footer?
// @FIXRES
add_action( 'wp_enqueue_scripts', function () {
    //FIXME Do in-page printed scripts need add_action()?

    $handle = 'HANDLE';
    wp_enqueue_style( $handle, $src, $deps, $ver, $media );
} );
```

#### Directly printed `<style>` element with inline stylesheet

```php
//FIXME Does it work in the footer?
// @FIXRES
add_action( 'wp_enqueue_scripts', function () {
    $footer_handle = 'HANDLE';
    // This will be placed AFTER the $footer_handle style.
    wp_add_inline_style( $footer_handle, $data );
} );
```

#### ???one more type of CSS failure

#### Add above-the-fold inline style

```php
// @FIXRES
add_action( 'wp_print_styles', function () {
    printf( '<style id="fixres-inline-css" type="text/css">%s</style>' . "\n",
        file_get_contents( get_stylesheet_directory() . '/style-inline.css' )
    );
}, 0 );
```

## Check pages

- Minit TOC.
- Functional tests.


## Final steps

- Comment changes `// @FIXRES `, `// @FIXRES - END`, don't remove superseded code.
- Examine scripts and styles without minification.
- Check all scripts and styles: Google Chrome/Audit, Google Chrome/PageSpeed, PageSpeed Insights.
- Create patch files `diff -rupNwB theme.orig/ theme/ > theme.patch`.
- Backup modified theme and plugins.

## Test cases

### WordPress standards

- #001 js file and inline script @wp_enqueue_scripts
- #002 ~ css
- #003 css file and multiple inline styles @wp_enqueue_scripts
- #004 js file in page
- #005 ~ css
- #007 js file and inline in page
- #008 ~ css

### Irregularities

- file before wp_print_s*, inline after wp_print_s*
- inline without file, see: Dummy handles above
- WordPress generated resources (*.css.php, *.js.php)
- Lost inline due to non-existent handle,

wp-includes/class.wp-dependencies.php:240
```php
			error_log( 'Lost inline: ' . $handle );
		}
```

- #501 js file @wp_enqueue_scripts, inline script in page
- #502 ~ css
- #503 js file @wp_enqueue_scripts, inline script @wp_footer
- #504 ~ css
- #507 js file @wp_footer
- #508 ~ css
- #... test #001-#003 at wp_head, wp_print_styles, wp_print_scripts ...
- #501 js file enqueued before template_redirect, inline script added in page before wp_footer.
- #502 css file enqueued before template_redirect, inline style added in page before wp_footer.

1. Write a special test theme with all the test cases, test number included in test data.
2. Run WordPress with ob_start( 'function' ).
3. Expect HTML output and minified files.
