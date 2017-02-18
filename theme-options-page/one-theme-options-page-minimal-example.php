<?php

/*
// In functions.php
if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
    require_once get_template_directory() . '/inc/class-one-theme-options-page.php';
    require_once get_template_directory() . '/inc/one-theme-options-page-example.php';
    // [8] EDIT: class name
    new Custom_Theme_Options_Page();
}
*/

// [1] EDIT: class name
// [2] EDIT: use your theme's text domain instead of "otop_textdomain"
class Custom_Theme_Minimal_Options_Page extends One_Theme_Options_Page {

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'inline_style' ), 20 );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    public function add_settings_page() {

        // [3] EDIT: menu slug, <h1> of the options page, HTML <title> (optional)
        // Add the options page for our theme
        $this->add_admin_menu(
            'one-theme-menu-slug',
            __( 'Theme options page H1', 'otop_textdomain' ),
            __( 'HTML title', 'otop_textdomain' )
        );
    }

    public function settings_init() {

        // [4] EDIT: first option name
        $option = 'one_theme_minimal';
        $this->register_option( $option );
        // [5] EDIT: first form section ID
        $section = 'one_theme_page_section';
        // [6] EDIT: section's <h2>, section description (optional)
        $this->add_settings_section(
            $section,
            __( 'Section title H2', 'otop_textdomain' ),
            __( 'Section description P', 'otop_textdomain' )
        );

        // Fields of the current section
        // Available args: label_for, class (for <tr>),
        //     (HTML) classes, default (value), required (boolean), description and various HTML attributes
        // [7] EDIT: fields
        $this->add_settings_field(
            /* section      */ $section,
            /* field ID     */ 'unique_text_field',
            /* type         */ 'text',
            /* sanitization */ 'htmltext',
            /* label        */ __( 'Text label', 'otop_textdomain' ),
            /* option       */ $option,
            /* args         */ array(
                'label_for' => true,
                'classes'   => 'regular-text',
                'required'  => true,
            )
        );
    }
}

// That's it.
