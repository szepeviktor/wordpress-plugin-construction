<?php

/*
// In functions.php
if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
    require_once get_template_directory() . '/inc/class-one-theme-options-page.php';
    require_once get_template_directory() . '/inc/one-theme-options-page-example.php';
    new Custom_Theme_Options_Page();
}
*/

class Custom_Theme_Options_Page extends One_Theme_Options_Page {

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'inline_style' ), 20 );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        add_filter( 'otop_inline_style', array( $this, 'floating_submit' ) );
        add_action( 'otop_after_form', array( $this, 'input_scrollend' ) );
    }

    public function add_settings_page() {

        $this->add_admin_menu(
            // EDIT
            'one-theme-menu-slug',
            __( 'One theme options page H1', 'otop_textdomain' ),
            __( 'HTML title', 'otop_textdomain' )
        );
    }

    public function settings_init() {

        /**
         * Current option name
         */
        // EDIT
        $option = 'one_theme_settings';
        $this->register_option( $option );

        /**
         * Missing option notice
         */
        if ( false === get_option( $option ) ) {
            add_action( 'otop_admin_notices', function () {
                add_settings_error(
                    'one-theme-page',
                    'missing',
                    __( 'Please save theme options to initialize values!', 'otop_textdomain' ),
                    'notice-warning'
                );
            } );
        }

        /**
         * Current section ID
         */
        // EDIT
        $section = 'one_theme_page_section';
        $this->add_settings_section(
            $section,
            // EDIT
            __( 'One theme section title H2', 'otop_textdomain' ),
            __( 'One theme section description P', 'otop_textdomain' )
        );

        $this->add_settings_field(
            $section,
            // EDIT
            'one_theme_text_field_0',
            'text',
            'htmltext',
            __( 'Text label', 'otop_textdomain' ),
            $option,
            // core: label_for, class for <tr>
            // otop: elements, classes, default, description, required and HTML attributes
            array(
                'label_for' => true,
                'default' => 'Some apples are red.',
                'placeholder' => 'Apples are ...',
                'description' => 'Please input apple colors.',
                // Width classes: tiny-text(35px) small-text(50px) ''(170px) regular-text(25em) all-options(250px) large-text(100%)
                // Other classes: code
                'classes' => 'regular-text code',
                'title' => 'This field is mandatory.',
                'required' => true,
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_text_field_u',
            'text',
            'url',
            __( 'URL label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'placeholder' => 'http://',
                'description' => 'Please enter a URL.',
                'classes' => 'regular-text code',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_text_field_t',
            'text',
            'htmltext',
            __( 'Large label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'default' => 'This is a read-only but selectable field',
                'classes' => 'large-text',
                // A readonly element is just not editable, but gets sent when the according form submits.
                // A disabled element isn't editable and isn't sent on submit.
                // Another difference is that readonly elements can be focused while disabled elements can't.
                'readonly' => true,
                'description' => 'May not be edited but its value is saved.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_checkbox_field_1',
            'checkbox',
            'one',
            __( 'Checkbox label', 'otop_textdomain' ),
            $option,
            array(
                'elements' => array(
                    'NameA',
                ),
                'description' => 'This is an on/off switch.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_checkbox_field_1m',
            'multicheckbox',
            'arrayone',
            __( 'Multi checkbox label', 'otop_textdomain' ),
            $option,
            array(
                // Array indices will be 0, 1, 2
                'elements' => array(
                    'Name1',
                    'Name2',
                    'Name3',
                ),
                // Array default
                'default' => array( '1', '0', '1' ),
                'description' => 'This is a nice descriptive line.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_radio_field_2',
            'radio',
            'elements',
            __( 'Radio label', 'otop_textdomain' ),
            $option,
            array(
                'elements' => array(
                    'key_1' => 'Name4',
                    'key_2' => 'Name5',
                    'key_3' => 'Name6',
                ),
                'description' => 'Pick one!',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_select_clientnum',
            'number',
            'integer',
            __( 'Client number', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'classes' => 'code regular-text',
                'description' => 'Integers only.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_select_pwd',
            'password',
            'htmltext',
            __( 'API key', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'required' => true,
                'classes' => 'regular-text code',
                'minlength' => 16,
                'description' => 'A password field.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_select_field_3',
            'select',
            'elements',
            __( 'Select label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'elements' => array(
                    '&ndash; Please select &ndash;' => false,
                    'key_4' => 'Name4',
                    'key_5' => 'Name5',
                    'key_6' => 'Name6',
                ),
                'style' => 'min-width: 364px;',
                'description' => 'Drop-down list.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_select_field_p',
            'post',
            'integer',
            __( 'WPCF7 form', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'query' => array(
                    'post_type' => 'wpcf7_contact_form',
                    'nopaging' => true,
                    'order' => 'ASC',
                ),
                'style' => 'min-width: 364px;',
                'description' => 'List of Contact Form 7 forms.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_select_field_3m',
            'multiselect',
            'arrayelements',
            __( 'Multiselect label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'elements' => array(
                    'key_4' => 'Name4',
                    'key_5' => 'Name5',
                    'key_6' => 'Name6',
                    'key_7' => 'Name7',
                ),
                'size' => '2',
                'style' => 'min-width: 364px;',
                'description' => 'You may select multiple items.',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_justhtml',
            'statichtml',
            null,
            __( 'WARNING!', 'otop_textdomain' ),
            'static',
            array(
                'content' => '<p>This is plain old <strong>static</strong> content. <span style="color: green;">No inputs.</span></p>'
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_textarea_field_4',
            'textarea',
            'htmltext',
            __( 'Textarea label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'cols' => 40,
                'rows' => 5,
                'style' => 'width: 26em; resize: none;',
                'description' => 'Enter multiline text.',
            )
        );

        /**
         * Multilingual option name
         *
         * Custom_Theme()->pll_get_field( 'two_theme_settings', 'two_theme_text_field_u' )
         */
        $option = 'two_theme_settings';
        $option .= '_' . $this->current_language();
        $this->register_option( $option );

        /**
         * Multilingual section ID
         */
        $section = 'two_theme_page_section';
        $this->add_settings_section(
            $section,
            __( 'Multilingual section title H2', 'otop_textdomain' ),
            __( 'Language dependent section description P', 'otop_textdomain' ) . ' ' . $this->current_language()
        );

        $this->add_settings_field(
            $section,
            'two_theme_text_field_0',
            'text',
            'htmltext',
            __( '2. Text label', 'otop_textdomain' ),
            $option,
            // label_for,elements,class(for <tr>),classes,default,description,rows,cols and custom values for custom field types
            array(
                'label_for' => true,
                'default' => 'Some apples are red.',
                'placeholder' => __( 'Apples are ...', 'otop_textdomain' ),
                'description' => __( 'Please input apple colors.', 'otop_textdomain' ),
                // Width classes: tiny-text(35px) small-text(50px) ''(170px) regular-text(25em) all-options(250px) large-text(100%)
                // Other classes: code
                'classes' => 'regular-text code',
                'title' => __( 'This field is mandatory.', 'otop_textdomain' ),
                'required' => true,
            )
        );

        $this->add_settings_field(
            $section,
            'two_theme_text_field_u',
            'text',
            'url',
            __( '2. URL label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'placeholder' => 'http://',
                'description' => __( 'Please enter target URL.', 'otop_textdomain' ),
                'classes' => 'regular-text code',
                'onfocus' => 'this.scrollLeft=this.scrollWidth;',
            )
        );

    }

    public function input_scrollend() {

        // Show the end of input content (file name of URL-s)
        $script = '<script>jQuery(".one-theme-page input[type=text]").each(function ()
            { this.scrollLeft = this.scrollWidth; });</script>';

        print $script;
    }

    public function floating_submit( $style ) {

        // Floating submit button, CSS3 { position:sticky; }
        $style .= '.one-theme-page #submit { position: fixed !important; bottom: 35px !important; }';

        return $style;
    }

    private function current_language() {

        if ( function_exists( 'pll_current_language' ) ) {
            $current_language = pll_current_language( 'locale' );
            if ( false === $current_language ) {
                $current_language = pll_default_language( 'locale' );
            }
        } else {
            $current_language = get_locale();
        }

        return $current_language;
    }
}
