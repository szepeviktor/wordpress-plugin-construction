<?php

require_once 'inc/one-theme-options-page.php';

class Custom_Theme extends One_Theme_Options_Page {

// TODO HTML output example as a shortcode [otop-example]: trim, esc_* ...
    public function settings_init() {

        $this->add_settings_tab(
            'one-theme-menu-slug',
            __( 'One theme options page H1', 'one_theme_textdomain' ),
            __( 'HTML/title' )
        );

        /**
         * Current option name
         */
        $option = 'one_theme_settings';
        register_setting( $this->page_slug, $option, array( $this, 'sanitize_option' ) );

        /**
         * Current section ID
         */
        $section = 'one_theme_page_section';
        $this->add_settings_section(
            $section,
            __( 'One theme section title H2', 'one_theme_textdomain' ),
            __( 'One theme section description p', 'one_theme_textdomain' )
        );

        $this->add_settings_field(
            $section,
            'one_theme_text_field_0',
            'text',
            'htmltext',
            __( 'Text label', 'one_theme_textdomain' ),
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
            'one_theme_text_field_u',
            'text',
            // TODO add scroll to the end of current value: onclick="jQuery(this).select();"
            'url',
            __( 'URL label', 'one_theme_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'placeholder' => 'http://',
                'description' => 'Please enter a URL.',
                'classes' => 'regular-text code',
                'onfocus' => 'this.scrollLeft=this.scrollWidth;',
            )
        );

        $this->add_settings_field(
            $section,
            'one_theme_text_field_t',
            'text',
            'htmltext',
            __( 'Tiny label', 'one_theme_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'default' => 'This is a read-only field',
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
            __( 'Checkbox label', 'one_theme_textdomain' ),
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
            __( 'Multi checkbox label', 'one_theme_textdomain' ),
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
            __( 'Radio label', 'one_theme_textdomain' ),
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
            __( 'Select label', 'one_theme_textdomain' ),
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
            __( 'Multiselect label', 'one_theme_textdomain' ),
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
            __( 'Textarea label', 'one_theme_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'cols' => 40,
                'rows' => 5,
                'style' => 'width: 25em; resize: none;',
            )
        );
    }
}

new Custom_Theme();
