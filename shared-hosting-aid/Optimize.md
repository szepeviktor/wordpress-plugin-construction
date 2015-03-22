# Website optimization

## Examine resources

Frontend Debugger
MinQueue

## Fix resource loading

### Javascript incidents

#### Directly printed `<script>` element with external script

```php
// Do in-page printed scripts need add_action()?
function fixres_enqueue_external_script_01() {
    wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer_true );
}

add_action( 'wp_enqueue_scripts', 'fixres_external_script_01' );
```

#### Directly printed `<script>` element with inline script

No inline Javascript in WordPress.

```php
/**
 * Introduce this script to WordPress.
 *
 * This will be placed BEFORE the $footer_handle script in the footer.
 * FIXME Will it be printed in the footer with a header script's handle?
 */
function fixres_callback_01( $buffer ) {
    /*
    global $fixed_resourced;
    $fixed_resourced[] = $buffer;
    */

    global $wp_scripts;
    $name = 'NAME-inline-script';
    $footer_handle = 'HANDLE';
    $fix_res_banner = "<!-- BEGIN {$name} -->\n";
    $fix_res_data = $wp_scripts->get_data( $footer_handle, 'data' )
        . $fix_res_banner . $buffer;
    $wp_scripts->add_data( $footer_handle, 'data', $fix_res_data );
    return "<!-- {$name} optimized -->\n";
}

// Wrap inline script printing
ob_start( 'fixres_callback_01' );
    // echo $script;
ob_end_flush();
```

#### Directly printed `<script>` element with data only

```php
wp_localize_script( $footer_handle, $js_var_name, $data );
```

### Stylesheet incidents

#### Directly printed `<link>` element with external stylesheet

```php
//FIXME Do in-page printed scripts need add_action()?
function fixres_enqueue_external_style_01() {
    $handle = 'HANDLE';
    wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

//FIXME Does it work in the footer?
add_action( 'wp_enqueue_scripts', 'fixres_enqueue_external_style_01' );
```

#### Directly printed `<style>` element with inline stylesheet

```php
function fixres_enqueue_inline_style_01() {
    $footer_handle = 'HANDLE';
    // This will be placed AFTER the $footer_handle style.
    wp_add_inline_style( $footer_handle, $data );
}

//FIXME Does it work in the footer?
add_action( 'wp_enqueue_scripts', 'fixres_enqueue_inline_style_01' );
```
