<?php

require_once 'includes/class-one-theme-options-page.php';

class Custom_Theme extends One_Theme_Options_Page {

    public function __construct() {

        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'settings_init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'inline_style' ), 20 );

        add_action( 'otop_after_form', array( $this, 'input_scrollend' ) );
        add_filter( 'otop_inline_style', array( $this, 'floating_submit' ) );
    }

    public function add_settings_page() {

        $this->add_admin_menu(
            'one-theme-menu-slug',
            __( 'One theme options page H1', 'otop_textdomain' ),
            __( 'HTML title', 'otop_textdomain' )
        );
    }

    public function settings_init() {

        /**
         * Current option name
         */
        $option = 'one_theme_settings';
        $this->register_option( $option );

        /**
         * Missing option notice
         */
        if ( false === get_option( $option ) ) {
            add_action( 'admin_notices', function () {

                // 'admin_init' is early to get current screen ID
                $screen = get_current_screen();
                if ( $this->wp_hook_suffix !== $screen->id ) {
                    return;
                }

                // See: wp-admin/css/common.css
                printf( '<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
                    esc_html( __( 'Please save settings to initialize values!', 'otop_textdomain' ) )
                );
            } );
        }

        /**
         * Current section ID
         */
        $section = 'one_theme_page_section';
        $this->add_settings_section(
            $section,
            __( 'One theme section title H2', 'otop_textdomain' ),
            __( 'One theme section description p', 'otop_textdomain' )
        );

        $this->add_settings_field(
            $section,
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
            __( 'Tiny label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'default' => 'This is a read-only but selectable field',
                'classes' => 'large-text',
                // A readonly element is just not editable, but gets sent when the according form submits.
                // A disabled element isn't editable and isn't sent on submit.
                // Another difference is that readonly elements can be focused while disabled elements can't.
                'readonly' => true,
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
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_checkbox_field_1m',
            'multi_checkbox',
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
                    'key_4' => 'Name4',
                    'key_5' => 'Name5',
                    'key_6' => 'Name6',
                ),
                'style' => 'min-width: 364px;',
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
            )
        );

        /**
         * Current option name
         */
        $option = 'two_theme_settings';
        $this->register_option( $option );

        /**
         * Current section ID
         */
        $section = 'two_theme_page_section';
        $this->add_settings_section(
            $section,
            __( 'two theme section title H2', 'otop_textdomain' ),
            __( 'two theme section description p', 'otop_textdomain' )
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
            'two_theme_text_field_u',
            'text',
            // TODO add scroll to the end of current value: onclick="jQuery(this).select();"
            'url',
            __( '2. URL label', 'otop_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'placeholder' => 'http://',
                'description' => 'Please enter a URL.',
                'classes' => 'regular-text code',
                'onfocus' => 'this.scrollLeft=this.scrollWidth;',
            )
        );

    }

    public function input_scrollend() {

        // Show the end of input content (file name of URL-s)
        $script = '<script>jQuery(".one-theme-page input[type=text]").each(function () {this.scrollLeft = this.scrollWidth;});</script>';

        print $script;
    }

    public function floating_submit( $style ) {

        // Floating submit button, CSS3 { position:sticky; }
        $style .= '.one-theme-page #submit { position: fixed !important; bottom: 35px !important; }';

        return $style;
    }
}

new Custom_Theme();
