<?php

require_once 'inc/class-one-theme-options-page.php';

class Custom_Theme_Minimal extends One_Theme_Options_Page {

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'inline_style' ), 20 );
    }

    public function add_settings_page() {

        $this->add_admin_menu(
            'one-theme-menu-slug',
            __( 'Theme options page H1', 'otop_textdomain' ),
            __( 'HTML title', 'otop_textdomain' )
        );
    }

    public function settings_init() {

        $option = 'one_theme_minimal';
        $this->register_option( $option );
        $section = 'one_theme_page_section';
        $this->add_settings_section(
            $section,
            __( 'Section title H2', 'otop_textdomain' ),
            __( 'Section description P', 'otop_textdomain' )
        );

        // Fields
        $this->add_settings_field(
            /* section      */ $section,
            /* field ID     */ 'unique_text_field',
            /* type         */ 'text',
            /* sanitization */ 'htmltext',
            /* labal        */ __( 'Text label', 'otop_textdomain' ),
            /* option       */ $option,
            /* args         */ array(
                'label_for' => true,
                'classes'   => 'regular-text',
                'required'  => true,
            )
        );
    }
}

new Custom_Theme_Minimal();
