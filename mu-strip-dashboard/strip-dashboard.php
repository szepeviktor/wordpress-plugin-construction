//TODO wordPPPress conten filter etc....


// Remove comments page in menu
function o1_disable_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'o1_disable_comments_admin_menu');

// Redirect any user trying to access comments page
function o1_disable_comments_admin_menu_redirect() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url());
        exit;
    }
}
add_action('admin_init', 'o1_disable_comments_admin_menu_redirect');

// Remove links from admin bar
function o1_remove_admin_bar_links() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');          // Remove the WordPress logo
     $wp_admin_bar->remove_menu('about');            // Remove the about WordPress link
     $wp_admin_bar->remove_menu('wporg');            // Remove the WordPress.org link
     $wp_admin_bar->remove_menu('documentation');    // Remove the WordPress documentation link
     $wp_admin_bar->remove_menu('support-forums');   // Remove the support forums link
     $wp_admin_bar->remove_menu('feedback');         // Remove the feedback link
    $wp_admin_bar->remove_menu('comments');         // Remove the comments link
}
add_action( 'wp_before_admin_bar_render', 'o1_remove_admin_bar_links' );


// Remove WP version from admin footer
function o1_remove_update_footer() {
    remove_filter( 'update_footer', 'core_update_footer' );
}
add_action( 'in_admin_footer', 'o1_remove_update_footer');

// Remove 'Thank you WP' from admin footer
function o1_webdesign($footer) {
    return '<span id="footer-thankyou">online1 - honlapkészítés</span>';
}
add_filter('admin_footer_text', 'o1_webdesign');

