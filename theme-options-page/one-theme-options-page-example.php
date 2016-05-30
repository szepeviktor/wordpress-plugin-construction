<?php

require_once 'inc/one-theme-options-page.php';

class Custom_Theme extends One_Theme_Options_Page {

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
            // label_for,elements,class/th,classes,default,description,rows,cols and custom values for custom field types
            array(
                'label_for' => true,
                'default' => 'Some apples are red.',
                'description' => 'This is a nice descriptive line.',
                // Witdh classes: tiny-text small-text '' regular-text large-text
                // Other classes: code
                'classes' => 'regular-text code',
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
                'default' => 'Can U see me?',
                'classes' => 'large-text',
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
            'elementindex',
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
            'elementkey',
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
            'elementkey',
            __( 'Select label', 'one_theme_textdomain' ),
            $option,
            array(
                'label_for' => true,
                'elements' => array(
                    'key_4' => 'Name4',
                    'key_5' => 'Name5',
                    'key_6' => 'Name6',
                ),
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
                'classes' => 'large-text',
            )
        );
    }

    // TODO Sanitize array( field => type ) from private registry <- add_settings_field()
    public function sanitize_option( $value ) {

        if ( is_array( $value ) ) {
            // Sanitize a URL
            if ( isset( $value['one_theme_text_field_0'] ) ) {
                $value['one_theme_text_field_0'] = esc_url_raw( $value['one_theme_text_field_0'] );
            }
            // Sanitize HTML text (no tags)
            if ( isset( $value['one_theme_text_field_0'] ) ) {
                $value['one_theme_text_field_4'] = wp_strip_all_tags( $value['one_theme_text_field_4'] );
            }
            $value = apply_filters( 'otop_sanitize_option', $value );
        }

        return $value;
    }
}

new Custom_Theme();
